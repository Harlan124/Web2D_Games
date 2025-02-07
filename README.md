# Web2D_Games

## 2D games playable on the web!

**on-hold for a rewrite**

* Play your favorite 2D games on web browsers, using HTML5 + CSS + JS.
* For slower-pace games with 5 to 10 FPS, or 0 FPS (Wait for input) games.
* Optimized to work on smart phones, tablets, and other touch screen devices.

## Demo Gameplay

[![Toushin Toshi 1 Demo](http://img.youtube.com/vi/Jumikw3BS7o/0.jpg)](http://www.youtube.com/watch?v=Jumikw3BS7o)

## Prerequisites

* PHP 7.0 or above
  * http://windows.php.net/downloads
* Minimalistic PHP-cli (command-line interface) executable with
  * zlib extension
  * json extension
* From PHP for Windows, you'll only need these 3 files from the zip file
  * php.exe
  * php7ts.dll or php7nts.dll
  * php.ini (renamed from php.ini-development)
    * edit the value for 'memory_limit' from `128M` to a higher value, e.g. `2048M`

## Installation

* Run php build-in web server
  * `php.exe  -S ADDRESS:PORT  -t DIR`
  * ADDRESS can be `localhost` or `127.0.0.1`
  * PORT is optional. (default 80)
  * -t is optional. (default DIR = current directory)
* Start your web browser, and go to ADDRESS:PORT as configured above
  * `chrome.exe  http://127.0.0.1:80/main.php`

## Game Status

* on-hold for a rewrite
