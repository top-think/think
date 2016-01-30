<?php
// +----------------------------------------------------------------------
// | TopThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think;

use think\console\command\Build as BuildCommand;
use think\console\command\Command;
use think\console\command\Help as HelpCommand;
use think\console\command\Lists as ListCommand;
use think\console\command\make\Controller as MakeControllerCommand;
use think\console\command\make\Model as MakeModelCommand;
use think\console\helper\Debug as DebugFormatterHelper;
use think\console\helper\Formatter as FormatterHelper;
use think\console\helper\Process as ProcessHelper;
use think\console\helper\Question as QuestionHelper;
use think\console\helper\Set as HelperSet;
use think\console\Input;
use think\console\input\Argument as InputArgument;
use think\console\input\Definition as InputDefinition;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\console\output\Stream;

class Console
{

    private $name;
    private $version;

    /** @var Command[] */
    private $commands = [];

    private $wantHelps = false;

    /** @var  Command */
    private $runningCommand;

    private $catchExceptions = true;
    private $autoExit        = true;
    private $definition;
    private $helperSet;
    private $terminalDimensions;
    private $defaultCommand;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->name    = $name;
        $this->version = $version;

        $this->defaultCommand = 'list';
        $this->helperSet      = $this->getDefaultHelperSet();
        $this->definition     = $this->getDefaultInputDefinition();

        foreach ($this->getDefaultCommands() as $command) {
            $this->add($command);
        }
    }

    /**
     * 执行当前的指令
     * @return int
     * @throws \Exception
     * @api
     */
    public function run()
    {
        $input  = new Input();
        $output = new Output();

        $this->configureIO($input, $output);

        try {
            $exitCode = $this->doRun($input, $output);
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }

            $this->renderException($e, $output->getErrorOutput());

            $exitCode = $e->getCode();
            if (is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if (0 === $exitCode) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        }

        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }

            exit($exitCode);
        }

        return $exitCode;
    }

    /**
     * 执行指令
     * @param Input  $input
     * @param Output $output
     * @return int
     */
    public function doRun(Input $input, Output $output)
    {
        if (true === $input->hasParameterOption(['--version', '-V'])) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        $name = $this->getCommandName($input);

        if (true === $input->hasParameterOption(['--help', '-h'])) {
            if (!$name) {
                $name  = 'help';
                $input = new Input(['help']);
            } else {
                $this->wantHelps = true;
            }
        }

        if (!$name) {
            $name  = $this->defaultCommand;
            $input = new Input([$this->defaultCommand]);
        }

        $command = $this->find($name);

        $this->runningCommand = $command;
        $exitCode             = $this->doRunCommand($command, $input, $output);
        $this->runningCommand = null;

        return $exitCode;
    }

    /**
     * 设置助手集
     * @param HelperSet $helperSet
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * 获取助手集
     * @return HelperSet
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * 设置输入参数定义
     * @param InputDefinition $definition
     */
    public function setDefinition(InputDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * 获取输入参数定义
     * @return InputDefinition The InputDefinition instance
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Gets the help message.
     * @return string A help message.
     */
    public function getHelp()
    {
        return $this->getLongVersion();
    }

    /**
     * 是否捕获异常
     * @param bool $boolean
     * @api
     */
    public function setCatchExceptions($boolean)
    {
        $this->catchExceptions = (bool) $boolean;
    }

    /**
     * 是否自动退出
     * @param bool $boolean
     * @api
     */
    public function setAutoExit($boolean)
    {
        $this->autoExit = (bool) $boolean;
    }

    /**
     * 获取名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 设置名称
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * 获取版本
     * @return string
     * @api
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * 设置版本
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * 获取完整的版本号
     * @return string
     */
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName() && 'UNKNOWN' !== $this->getVersion()) {
            return sprintf('<info>%s</info> version <comment>%s</comment>', $this->getName(), $this->getVersion());
        }

        return '<info>Console Tool</info>';
    }

    /**
     * 注册一个指令
     * @param string $name
     * @return Command
     */
    public function register($name)
    {
        return $this->add(new Command($name));
    }

    /**
     * 添加指令
     * @param Command[] $commands
     */
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    /**
     * 添加一个指令
     * @param Command $command
     * @return Command
     */
    public function add(Command $command)
    {
        $command->setConsole($this);

        if (!$command->isEnabled()) {
            $command->setConsole(null);
            return null;
        }

        if (null === $command->getDefinition()) {
            throw new \LogicException(sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', get_class($command)));
        }

        $this->commands[$command->getName()] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }

        return $command;
    }

    /**
     * 获取指令
     * @param string $name 指令名称
     * @return Command
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        if (!isset($this->commands[$name])) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $command = $this->commands[$name];

        if ($this->wantHelps) {
            $this->wantHelps = false;

            /** @var HelpCommand $helpCommand */
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);

            return $helpCommand;
        }

        return $command;
    }

    /**
     * 某个指令是否存在
     * @param string $name 指令民初
     * @return bool
     */
    public function has($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * 获取所有的命名空间
     * @return array
     */
    public function getNamespaces()
    {
        $namespaces = [];
        foreach ($this->commands as $command) {
            $namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));

            foreach ($command->getAliases() as $alias) {
                $namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
            }
        }

        return array_values(array_unique(array_filter($namespaces)));
    }

    /**
     * 查找注册命名空间中的名称或缩写。
     * @param string $namespace
     * @return string
     * @throws \InvalidArgumentException
     */
    public function findNamespace($namespace)
    {
        $allNamespaces = $this->getNamespaces();
        $expr          = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $namespace);
        $namespaces = preg_grep('{^' . $expr . '}', $allNamespaces);

        if (empty($namespaces)) {
            $message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);

            if ($alternatives = $this->findAlternatives($namespace, $allNamespaces)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }

                $message .= implode("\n    ", $alternatives);
            }

            throw new \InvalidArgumentException($message);
        }

        $exact = in_array($namespace, $namespaces, true);
        if (count($namespaces) > 1 && !$exact) {
            throw new \InvalidArgumentException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))));
        }

        return $exact ? $namespace : reset($namespaces);
    }

    /**
     * 查找指令
     * @param string $name 名称或者别名
     * @return Command
     * @throws \InvalidArgumentException
     */
    public function find($name)
    {
        $allCommands = array_keys($this->commands);
        $expr        = preg_replace_callback('{([^:]+|)}', function ($matches) {
            return preg_quote($matches[1]) . '[^:]*';
        }, $name);
        $commands = preg_grep('{^' . $expr . '}', $allCommands);

        if (empty($commands) || count(preg_grep('{^' . $expr . '$}', $commands)) < 1) {
            if (false !== $pos = strrpos($name, ':')) {
                $this->findNamespace(substr($name, 0, $pos));
            }

            $message = sprintf('Command "%s" is not defined.', $name);

            if ($alternatives = $this->findAlternatives($name, $allCommands)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= implode("\n    ", $alternatives);
            }

            throw new \InvalidArgumentException($message);
        }

        if (count($commands) > 1) {
            $commandList = $this->commands;
            $commands    = array_filter($commands, function ($nameOrAlias) use ($commandList, $commands) {
                $commandName = $commandList[$nameOrAlias]->getName();

                return $commandName === $nameOrAlias || !in_array($commandName, $commands);
            });
        }

        $exact = in_array($name, $commands, true);
        if (count($commands) > 1 && !$exact) {
            $suggestions = $this->getAbbreviationSuggestions(array_values($commands));

            throw new \InvalidArgumentException(sprintf('Command "%s" is ambiguous (%s).', $name, $suggestions));
        }

        return $this->get($exact ? $name : reset($commands));
    }

    /**
     * 获取所有的指令
     * @param string $namespace 命名空间
     * @return Command[]
     * @api
     */
    public function all($namespace = null)
    {
        if (null === $namespace) {
            return $this->commands;
        }

        $commands = [];
        foreach ($this->commands as $name => $command) {
            if ($this->extractNamespace($name, substr_count($namespace, ':') + 1) === $namespace) {
                $commands[$name] = $command;
            }
        }

        return $commands;
    }

    /**
     * 获取可能的指令名
     * @param array $names
     * @return array
     */
    public static function getAbbreviations($names)
    {
        $abbrevs = [];
        foreach ($names as $name) {
            for ($len = strlen($name); $len > 0; --$len) {
                $abbrev             = substr($name, 0, $len);
                $abbrevs[$abbrev][] = $name;
            }
        }

        return $abbrevs;
    }

    /**
     * 呈现捕获的异常
     * @param \Exception $e
     * @param Stream     $output
     */
    public function renderException(\Exception $e, Stream $output)
    {
        do {
            $title = sprintf('  [%s]  ', get_class($e));

            $len = $this->stringWidth($title);

            $width = $this->getTerminalWidth() ? $this->getTerminalWidth() - 1 : PHP_INT_MAX;

            if (defined('HHVM_VERSION') && $width > 1 << 31) {
                $width = 1 << 31;
            }
            $formatter = $output->getFormatter();
            $lines     = [];
            foreach (preg_split('/\r?\n/', $e->getMessage()) as $line) {
                foreach ($this->splitStringByWidth($line, $width - 4) as $line) {

                    $lineLength = $this->stringWidth(preg_replace('/\[[^m]*m/', '', $formatter->format($line))) + 4;
                    $lines[]    = [$line, $lineLength];

                    $len = max($lineLength, $len);
                }
            }

            $messages   = ['', ''];
            $messages[] = $emptyLine = $formatter->format(sprintf('<error>%s</error>', str_repeat(' ', $len)));
            $messages[] = $formatter->format(sprintf('<error>%s%s</error>', $title, str_repeat(' ', max(0, $len - $this->stringWidth($title)))));
            foreach ($lines as $line) {
                $messages[] = $formatter->format(sprintf('<error>  %s  %s</error>', $line[0], str_repeat(' ', $len - $line[1])));
            }
            $messages[] = $emptyLine;
            $messages[] = '';
            $messages[] = '';

            $output->writeln($messages, Output::OUTPUT_RAW);

            if (Output::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<comment>Exception trace:</comment>');

                // exception related properties
                $trace = $e->getTrace();
                array_unshift($trace, [
                    'function' => '',
                    'file'     => $e->getFile() !== null ? $e->getFile() : 'n/a',
                    'line'     => $e->getLine() !== null ? $e->getLine() : 'n/a',
                    'args'     => [],
                ]);

                for ($i = 0, $count = count($trace); $i < $count; ++$i) {
                    $class    = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
                    $type     = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
                    $function = $trace[$i]['function'];
                    $file     = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
                    $line     = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

                    $output->writeln(sprintf(' %s%s%s() at <info>%s:%s</info>', $class, $type, $function, $file, $line));
                }

                $output->writeln('');
                $output->writeln('');
            }
        } while ($e = $e->getPrevious());

        if (null !== $this->runningCommand) {
            $output->writeln(sprintf('<info>%s</info>', sprintf($this->runningCommand->getSynopsis(), $this->getName())));
            $output->writeln('');
            $output->writeln('');
        }
    }

    /**
     * 获取终端宽度
     * @return int|null
     */
    protected function getTerminalWidth()
    {
        $dimensions = $this->getTerminalDimensions();

        return $dimensions[0];
    }

    /**
     * 获取终端高度
     * @return int|null
     */
    protected function getTerminalHeight()
    {
        $dimensions = $this->getTerminalDimensions();

        return $dimensions[1];
    }

    /**
     * 获取当前终端的尺寸
     * @return array
     */
    public function getTerminalDimensions()
    {
        if ($this->terminalDimensions) {
            return $this->terminalDimensions;
        }

        if ('\\' === DS) {
            if (preg_match('/^(\d+)x\d+ \(\d+x(\d+)\)$/', trim(getenv('ANSICON')), $matches)) {
                return [(int) $matches[1], (int) $matches[2]];
            }
            if (preg_match('/^(\d+)x(\d+)$/', $this->getConsoleMode(), $matches)) {
                return [(int) $matches[1], (int) $matches[2]];
            }
        }

        if ($sttyString = $this->getSttyColumns()) {
            if (preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyString, $matches)) {
                return [(int) $matches[2], (int) $matches[1]];
            }
            if (preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyString, $matches)) {
                return [(int) $matches[2], (int) $matches[1]];
            }
        }

        return [null, null];
    }

    /**
     * 设置终端尺寸
     * @param int $width
     * @param int $height
     * @return Console
     */
    public function setTerminalDimensions($width, $height)
    {
        $this->terminalDimensions = [$width, $height];

        return $this;
    }

    /**
     * 配置基于用户的参数和选项的输入和输出实例。
     * @param Input  $input  输入实例
     * @param Output $output 输出实例
     */
    protected function configureIO(Input $input, Output $output)
    {
        if (true === $input->hasParameterOption(['--ansi'])) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(['--no-ansi'])) {
            $output->setDecorated(false);
        }

        if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
            $input->setInteractive(false);
        } elseif (function_exists('posix_isatty') && $this->getHelperSet()->has('question')) {
            $inputStream = $this->getHelperSet()->get('question')->getInputStream();
            if (!@posix_isatty($inputStream) && false === getenv('SHELL_INTERACTIVE')) {
                $input->setInteractive(false);
            }
        }

        if (true === $input->hasParameterOption(['--quiet', '-q'])) {
            $output->setVerbosity(Output::VERBOSITY_QUIET);
        } else {
            if ($input->hasParameterOption('-vvv') || $input->hasParameterOption('--verbose=3')
                || $input->getParameterOption('--verbose') === 3
            ) {
                $output->setVerbosity(Output::VERBOSITY_DEBUG);
            } elseif ($input->hasParameterOption('-vv') || $input->hasParameterOption('--verbose=2')
                || $input->getParameterOption('--verbose') === 2
            ) {
                $output->setVerbosity(Output::VERBOSITY_VERY_VERBOSE);
            } elseif ($input->hasParameterOption('-v') || $input->hasParameterOption('--verbose=1')
                || $input->hasParameterOption('--verbose')
                || $input->getParameterOption('--verbose')
            ) {
                $output->setVerbosity(Output::VERBOSITY_VERBOSE);
            }
        }
    }

    /**
     * 执行指令
     * @param Command $command 指令实例
     * @param Input   $input   输入实例
     * @param Output  $output  输出实例
     * @return int
     * @throws \Exception
     */
    protected function doRunCommand(Command $command, Input $input, Output $output)
    {
        return $command->run($input, $output);
    }

    /**
     * 获取指令的基础名称
     * @param Input $input
     * @return string
     */
    protected function getCommandName(Input $input)
    {
        return $input->getFirstArgument();
    }

    /**
     * 获取默认输入定义
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this console version'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    /**
     * 设置默认命令
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        return [
            new HelpCommand(),
            new ListCommand(),
            new MakeControllerCommand(),
            new MakeModelCommand(),
            new BuildCommand(),
        ];
    }

    /**
     * 设置默认助手
     * @return HelperSet
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet([
            new FormatterHelper(),
            new DebugFormatterHelper(),
            new ProcessHelper(),
            new QuestionHelper(),
        ]);
    }

    /**
     * 获取stty列数
     * @return string
     */
    private function getSttyColumns()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process        = proc_open('stty -a | grep columns', $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $info;
        }
        return null;
    }

    /**
     * 获取终端模式
     * @return string <width>x<height> 或 null
     */
    private function getConsoleMode()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $descriptorspec = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process        = proc_open('mode CON', $descriptorspec, $pipes, null, null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            if (preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $matches)) {
                return $matches[2] . 'x' . $matches[1];
            }
        }
        return null;
    }

    /**
     * 获取可能的建议
     * @param array $abbrevs
     * @return string
     */
    private function getAbbreviationSuggestions($abbrevs)
    {
        return sprintf('%s, %s%s', $abbrevs[0], $abbrevs[1], count($abbrevs) > 2 ? sprintf(' and %d more', count($abbrevs) - 2) : '');
    }

    /**
     * 返回命名空间部分
     * @param string $name  指令
     * @param string $limit 部分的命名空间的最大数量
     * @return string
     */
    public function extractNamespace($name, $limit = null)
    {
        $parts = explode(':', $name);
        array_pop($parts);

        return implode(':', null === $limit ? $parts : array_slice($parts, 0, $limit));
    }

    /**
     * 查找可替代的建议
     * @param string             $name
     * @param array|\Traversable $collection
     * @return array
     */
    private function findAlternatives($name, $collection)
    {
        $threshold    = 1e3;
        $alternatives = [];

        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = explode(':', $item);
        }

        foreach (explode(':', $name) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }

                $lev = levenshtein($subname, $parts[$i]);
                if ($lev <= strlen($subname) / 3 || '' !== $subname && false !== strpos($parts[$i], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev;
            }
        }

        $alternatives = array_filter($alternatives, function ($lev) use ($threshold) {
            return $lev < 2 * $threshold;
        });
        asort($alternatives);

        return array_keys($alternatives);
    }

    /**
     * 设置默认的指令
     * @param string $commandName The Command name
     */
    public function setDefaultCommand($commandName)
    {
        $this->defaultCommand = $commandName;
    }

    private function stringWidth($string)
    {
        if (!function_exists('mb_strwidth')) {
            return strlen($string);
        }

        if (false === $encoding = mb_detect_encoding($string)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    private function splitStringByWidth($string, $width)
    {
        if (!function_exists('mb_strwidth')) {
            return str_split($string, $width);
        }

        if (false === $encoding = mb_detect_encoding($string)) {
            return str_split($string, $width);
        }

        $utf8String = mb_convert_encoding($string, 'utf8', $encoding);
        $lines      = [];
        $line       = '';
        foreach (preg_split('//u', $utf8String) as $char) {
            if (mb_strwidth($line . $char, 'utf8') <= $width) {
                $line .= $char;
                continue;
            }
            $lines[] = str_pad($line, $width);
            $line    = $char;
        }
        if (strlen($line)) {
            $lines[] = count($lines) ? str_pad($line, $width) : $line;
        }

        mb_convert_variables($encoding, 'utf8', $lines);

        return $lines;
    }

    /**
     * 返回所有的命名空间
     * @param string $name
     * @return array
     */
    private function extractAllNamespaces($name)
    {
        $parts      = explode(':', $name, -1);
        $namespaces = [];

        foreach ($parts as $part) {
            if (count($namespaces)) {
                $namespaces[] = end($namespaces) . ':' . $part;
            } else {
                $namespaces[] = $part;
            }
        }

        return $namespaces;
    }

}
