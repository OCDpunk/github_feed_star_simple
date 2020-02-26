# 关于项目
github总会有很多好的项目，关注的大佬们每天都在star优秀的项目。于是有了这个脚本方便日后的工作学习。
# 用法
1. 修改 `$userName` 变量的值，修改为自己的github的名称。
2. 执行 `php star.php OAUTH-TOKEN 2>&1 | tee -a star.log` OAUTH-TOKEN 是github的token，具体参考：[authentication](https://developer.github.com/v3/#user-agent-required)。
