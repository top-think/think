## 基本流程

1. fork本项目；
2. 克隆（clone）你 fork 的项目到本地；
3. 新建分支（branch）并检出（checkout）新分支；
4. 添加本项目到你的本地 git 仓库作为上游（upstream）；
5. 进行修改，若你的修改包含方法或函数的增减，请记得修改[单元测试文件](tests)；
6. 变基（衍合 rebase）你的分支到上游 master 分支；
7. push 你的本地仓库到 github；
8. 提交 pull requests；
9. 等待 CI 验证（若不通过则重复 5~7，github 会自动更新你的 pull requests）；
10. 等待管理员处理，并及时 rebase 你的分支到上游 master 分支（若上游 master 分支有修改），若有必要，可以 `git push -f` 强行推送 rebase 后的分支到自己的 github fork。

## 注意事项

* 本项目代码格式化标准选用 **PSR-2**；
* 类名和类文件名遵循 **PSR-4**；
* 若对上述修改流程有任何不清楚的地方，请查阅 GIT 教程，如 [这个](http://backlogtool.com/git-guide/cn/)；
* 管理员不会合并造成 CI faild 的修改，若出现 CI faild 请检查自己的源代码或修改相应的[单元测试文件](tests)；
* 对于代码**不同方面**的修改，请在自己 fork 的项目中**创建不同的分支**（原因参见`修改流程`第9条备注部分）；
* 对于 Issues 的处理，请在 pull requests 时使用诸如 `fix #xxx(Issue ID)` 的 title 直接关闭 issue。
* 变基及交互式变基操作参见 [Git 交互式变基](http://pakchoi.me/2015/03/17/git-interactive-rebase/)
