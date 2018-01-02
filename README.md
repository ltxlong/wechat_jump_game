# wechat_jump_game
微信跳一跳辅助 PHP版本 ~~最好的语言~~ 

## 工具介绍

- PHP 5.3+
- gd拓展库
- Adb 驱动，可以到[这里](https://adb.clockworkmod.com/)下载
- android手机系统

## 安卓手机操作步骤

- 安卓手机打开USB调试，设置->开发者选项->USB调试
- 电脑与手机USB线连接，确保执行`adb devices`可以找到设备id
- 界面转至微信跳一跳游戏，点击开始游戏
- 打开电脑命令窗口，并在php环境路径下运行jump.php
- 运行`php jump.php`，如果手机界面显示USB授权，请点击确认

## PS:目前本脚本暂时只支持安卓，没有安卓手机的可以用安卓模拟器

## 参考

|项目|作者|
|---|---|
|[教你用Python来玩微信跳一跳](https://github.com/wangshub/wechat_jump_game)|[@wangshub](https://github.com/wangshub)|
|[JumpJumpHelper](https://github.com/metowolf/JumpJumpHelper)|[@metowolf](https://github.com/metowolf)|
|[教你用PHP来玩微信跳一跳](https://github.com/xianyunyh/wechat_jump_game)|[@xianyunyh](https://github.com/xianyunyh)|







