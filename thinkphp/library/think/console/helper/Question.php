<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\console\helper;


use think\console\Input;
use think\console\Output;
use think\console\helper\question\Question as OutputQuestion;
use think\console\helper\question\Choice as ChoiceQuestion;
use think\console\output\formatter\Style as OutputFormatterStyle;

class Question extends Helper
{

    private        $inputStream;
    private static $shell;
    private static $stty;

    /**
     * 向用户提问
     * @param Input          $input
     * @param Output         $output
     * @param OutputQuestion $question
     * @return string
     */
    public function ask(Input $input, Output $output, OutputQuestion $question)
    {
        if (!$input->isInteractive()) {
            return $question->getDefault();
        }

        if (!$question->getValidator()) {
            return $this->doAsk($output, $question);
        }

        $interviewer = function () use ($output, $question) {
            return $this->doAsk($output, $question);
        };

        return $this->validateAttempts($interviewer, $output, $question);
    }

    /**
     * 设置输入流
     * @param resource $stream
     * @throws \InvalidArgumentException
     */
    public function setInputStream($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Input stream must be a valid resource.');
        }

        $this->inputStream = $stream;
    }

    /**
     * 获取输入流
     * @return resource
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'question';
    }

    /**
     * 提问
     * @param Output         $output
     * @param OutputQuestion $question
     * @return bool|mixed|null|string
     * @throws \Exception
     * @throws \RuntimeException
     */
    private function doAsk(Output $output, OutputQuestion $question)
    {
        $this->writePrompt($output, $question);

        $inputStream  = $this->inputStream ?: STDIN;
        $autocomplete = $question->getAutocompleterValues();

        if (null === $autocomplete || !$this->hasSttyAvailable()) {
            $ret = false;
            if ($question->isHidden()) {
                try {
                    $ret = trim($this->getHiddenResponse($output, $inputStream));
                } catch (\RuntimeException $e) {
                    if (!$question->isHiddenFallback()) {
                        throw $e;
                    }
                }
            }

            if (false === $ret) {
                $ret = fgets($inputStream, 4096);
                if (false === $ret) {
                    throw new \RuntimeException('Aborted');
                }
                $ret = trim($ret);
            }
        } else {
            $ret = trim($this->autocomplete($output, $question, $inputStream));
        }

        $ret = strlen($ret) > 0 ? $ret : $question->getDefault();

        if ($normalizer = $question->getNormalizer()) {
            return $normalizer($ret);
        }

        return $ret;
    }

    /**
     * 显示提示
     * @param Output         $output
     * @param OutputQuestion $question
     */
    protected function writePrompt(Output $output, OutputQuestion $question)
    {
        $message = $question->getQuestion();

        if ($question instanceof ChoiceQuestion) {
            $width = max(array_map('strlen', array_keys($question->getChoices())));

            $messages = (array)$question->getQuestion();
            foreach ($question->getChoices() as $key => $value) {
                $messages[] = sprintf("  [<info>%-${width}s</info>] %s", $key, $value);
            }

            $output->writeln($messages);

            $message = $question->getPrompt();
        }

        $output->write($message);
    }

    /**
     * 输出错误
     * @param Output     $output
     * @param \Exception $error
     */
    protected function writeError(Output $output, \Exception $error)
    {
        if (null !== $this->getHelperSet() && $this->getHelperSet()->has('formatter')) {
            $message = $this->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error');
        } else {
            $message = '<error>' . $error->getMessage() . '</error>';
        }

        $output->writeln($message);
    }

    /**
     * 自动完成问题
     * @param Output         $output
     * @param OutputQuestion $question
     * @param                $inputStream
     * @return string
     */
    private function autocomplete(Output $output, OutputQuestion $question, $inputStream)
    {
        $autocomplete = $question->getAutocompleterValues();
        $ret          = '';

        $i          = 0;
        $ofs        = -1;
        $matches    = $autocomplete;
        $numMatches = count($matches);

        $sttyMode = shell_exec('stty -g');

        shell_exec('stty -icanon -echo');

        $output->getFormatter()->setStyle('hl', new OutputFormatterStyle('black', 'white'));

        while (!feof($inputStream)) {
            $c = fread($inputStream, 1);

            if ("\177" === $c) {
                if (0 === $numMatches && 0 !== $i) {
                    $i--;
                    $output->write("\033[1D");
                }

                if ($i === 0) {
                    $ofs        = -1;
                    $matches    = $autocomplete;
                    $numMatches = count($matches);
                } else {
                    $numMatches = 0;
                }

                $ret = substr($ret, 0, $i);
            } elseif ("\033" === $c) {
                $c .= fread($inputStream, 2);

                if (isset($c[2]) && ('A' === $c[2] || 'B' === $c[2])) {
                    if ('A' === $c[2] && -1 === $ofs) {
                        $ofs = 0;
                    }

                    if (0 === $numMatches) {
                        continue;
                    }

                    $ofs += ('A' === $c[2]) ? -1 : 1;
                    $ofs = ($numMatches + $ofs) % $numMatches;
                }
            } elseif (ord($c) < 32) {
                if ("\t" === $c || "\n" === $c) {
                    if ($numMatches > 0 && -1 !== $ofs) {
                        $ret = $matches[$ofs];
                        $output->write(substr($ret, $i));
                        $i = strlen($ret);
                    }

                    if ("\n" === $c) {
                        $output->write($c);
                        break;
                    }

                    $numMatches = 0;
                }

                continue;
            } else {
                $output->write($c);
                $ret .= $c;
                $i++;

                $numMatches = 0;
                $ofs        = 0;

                foreach ($autocomplete as $value) {
                    if (0 === strpos($value, $ret) && $i !== strlen($value)) {
                        $matches[$numMatches++] = $value;
                    }
                }
            }

            $output->write("\033[K");

            if ($numMatches > 0 && -1 !== $ofs) {
                $output->write("\0337");
                $output->write('<hl>' . substr($matches[$ofs], $i) . '</hl>');
                $output->write("\0338");
            }
        }

        shell_exec(sprintf('stty %s', $sttyMode));

        return $ret;
    }

    /**
     * 从用户获取隐藏的响应
     * @param Output $output
     * @return string
     * @throws \RuntimeException
     */
    private function getHiddenResponse(Output $output, $inputStream)
    {
        if ('\\' === DS) {
            $exe = __DIR__ . '/../bin/hiddeninput.exe';

            if ('phar:' === substr(__FILE__, 0, 5)) {
                $tmpExe = sys_get_temp_dir() . '/hiddeninput.exe';
                copy($exe, $tmpExe);
                $exe = $tmpExe;
            }

            $value = rtrim(shell_exec($exe));
            $output->writeln('');

            if (isset($tmpExe)) {
                unlink($tmpExe);
            }

            return $value;
        }

        if ($this->hasSttyAvailable()) {
            $sttyMode = shell_exec('stty -g');

            shell_exec('stty -echo');
            $value = fgets($inputStream, 4096);
            shell_exec(sprintf('stty %s', $sttyMode));

            if (false === $value) {
                throw new \RuntimeException('Aborted');
            }

            $value = trim($value);
            $output->writeln('');

            return $value;
        }

        if (false !== $shell = $this->getShell()) {
            $readCmd = $shell === 'csh' ? 'set mypassword = $<' : 'read -r mypassword';
            $command = sprintf("/usr/bin/env %s -c 'stty -echo; %s; stty echo; echo \$mypassword'", $shell, $readCmd);
            $value   = rtrim(shell_exec($command));
            $output->writeln('');

            return $value;
        }

        throw new \RuntimeException('Unable to hide the response.');
    }

    /**
     * 验证重试次数
     * @param callable       $interviewer
     * @param Output         $output
     * @param OutputQuestion $question
     * @return string
     * @throws null
     */
    private function validateAttempts($interviewer, Output $output, OutputQuestion $question)
    {
        $error    = null;
        $attempts = $question->getMaxAttempts();
        while (null === $attempts || $attempts--) {
            if (null !== $error) {
                $this->writeError($output, $error);
            }

            try {
                return call_user_func($question->getValidator(), $interviewer());
            } catch (\Exception $error) {
            }
        }

        throw $error;
    }

    /**
     * 获取一个有效的 unix 终端。
     * @return string|bool
     */
    private function getShell()
    {
        if (null !== self::$shell) {
            return self::$shell;
        }

        self::$shell = false;

        if (file_exists('/usr/bin/env')) {
            // handle other OSs with bash/zsh/ksh/csh if available to hide the answer
            $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";
            foreach (['bash', 'zsh', 'ksh', 'csh'] as $sh) {
                if ('OK' === rtrim(shell_exec(sprintf($test, $sh)))) {
                    self::$shell = $sh;
                    break;
                }
            }
        }

        return self::$shell;
    }

    /**
     * 检查有用的stty
     * @return bool
     */
    private function hasSttyAvailable()
    {
        if (null !== self::$stty) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = $exitcode === 0;
    }
}