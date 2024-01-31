=== Floating Window Music Player ===
Contributors:      Eric Zane
Donate link:       http://eric.cn.com/?p=1187
Tags:              music, player, html5, Netease, Tencent, QQ, Xiami, Baidu, 163, Kugou, 网易, 虾米, 百度, 酷狗 , floating window
Requires at least: 3.0.1
Tested up to:      5.9
Stable tag:        trunk
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

浮窗音乐播放器支持网易、QQ、虾米、百度、酷狗和酷我等音乐歌单的在线播放。

== Description ==
* This plugin can play Netease / Tencent / Xiami / Baidu / Kugou / Kuwo music in a floating window.
* 本插件支持网易、QQ、虾米、百度、酷狗和酷我等音乐歌单的在线播放。
* Demo 演示: http://eric.cn.com/
* Help 详细说明: http://eric.cn.com/?p=1187
* It calls the third-party API(Meting) to get song's information.
* 本插件使用了第三方API接口（Meting）以获取歌曲信息。
* Netease music 网易云音乐: http://music.163.com/
* Tencent music QQ音乐: http://y.qq.com/
* Xiami music 虾米音乐: http://www.xiami.com/
* Baidu music 百度音乐: http://music.baidu.com/
* Kugou music 酷狗音乐: http://www.kugou.com/
* Kuwo music 酷我音乐：http://www.kuwo.cn/

== Installation ==
* 上传插件文件到/wp-content/plugins/目录，或者直接在后台搜索安装
* 在后台插件菜单激活该插件
* 在仪表盘 - 音乐播放器 进行插件设置

== Frequently Asked Questions ==
= 无法正常播放怎么办？ =
* 尝试在后台开启加载基础库。
= 播放器没显示图标怎么办？ =
* 在后台开启加载图标库。
= 如何在播放时不被刷新打断？ =
* 在后台开启AJAX防刷新。
= 为何在非登录状态下无法播放歌曲？ =
* 根据非完全测试，某些AJAX插件如Advanced AJAX Page Loader会对调用Wordpress自身的AJAX造成影响，请尽量替换使用插件自带的AJAX。
= 我的网站的播放按钮位置不居中或字体过大/过小怎么办？ =
* 在后台自定义CSS里按示例进行调整。

== Screenshots ==
1. 前端效果
2. 后台效果（中文）
3. 后台效果（英文）

== Changelog ==
= 3.4.2 (2022/02/04) =
* 更新jquery、Font Awesome。
= 3.4.1 (2021/12/06) =
* 更新Meting框架至1.5.12。
= 3.4.0 (2021/07/19) =
* 更新Meting框架至1.5.11；
* 更新后台设置。
= 请移步插件主页查看更多107个修订历史 =
* http://eric.cn.com/?p=1187

== Upgrade Notice ==
* 更新jquery、Font Awesome。

== Arbitrary section ==
* 首次安装后需要保存一下设置；
* 后台所有文本框选项如需清空已填内容请填入1个空格，否则无法保存；
* 本插件与同样使用Meting模块的其他插件不相容，如：Hermit X、Netease Music、WP-Player等，请只同时启用其中1个插件。