<?php
//参数配置：
define('PRESS_COEFFICIENT', 1.392);//press_coefficient ：长按的时间系数，请自己根据实际情况调节
define('PIECE_BASE_HEIGHT_1_2', 20);//piece_base_height_1_2 : 二分之一的棋子底座高度，请自己根据实际情况调节
define('PIECE_BODY_WIDTH', 70);//piece_body_width ：棋子底座宽度，比截图中量到的稍微大一点比较安全，请自己根据实际情况调节
define('SLEEP_TIME', mt_rand(20,25)/10);//停留时间2秒，不用调（有的盒子上面停留2秒会额外加5~30分。如果停留时间与VAC有关，可以改为大于2的随机数，此时唯一的干扰是音乐盒的音符，小概率）。

/**
 * # === 思路 ===
 * # 核心：每次落稳之后截图，根据截图算出棋子的坐标和下一个块顶面的中点坐标，
 * #      根据两个点的距离乘以一个时间系数获得长按的时间
 * # 识别棋子：靠棋子的颜色来识别位置，通过截图发现最下面一行大概是一条直线，就从上往下一行一行遍历，
 * #         比较颜色（颜色用了一个区间来比较）找到最下面的那一行的所有点，然后求个中点，
 * #         求好之后再让 Y 轴坐标减小棋子底盘的一半高度从而得到中心点的坐标
 * # 识别棋盘：靠底色和方块的色差来做，从分数之下的位置开始，一行一行扫描，由于圆形的块最顶上是一条线，
 * #          方形的上面大概是一个点，所以就用类似识别棋子的做法多识别了几个点求中点，
 * #          这时候得到了块中点的 X 轴坐标，这时候假设现在棋子在当前块的中心，
 * #          根据一个通过截图获取的固定的角度来推出中点的 Y 坐标
 * # 最后：根据两点的坐标算距离乘以系数来获取长按时间（似乎可以直接用 X 轴距离）
 * 
 * # TODO: 解决定位偏移的问题
 * # TODO: 看看两个块中心到中轴距离是否相同，如果是的话靠这个来判断一下当前超前还是落后，便于矫正
 * # TODO: 一些固定值根据截图的具体大小计算
 * # TODO: 直接用 X 轴距离简化逻辑
 */

/**
 * 参数参考：
 * swipe参数的精确度不重要，用于自动重复开始游戏
 * 
 * 1280x720机型：
 *   "press_coefficient": 2.099,
 *   "piece_base_height_1_2": 13,
 *   "piece_body_width": 47,
 *   "swipe" : {
 *     "x1": 374,
 *     "y1": 1060,
 *     "x2": 374,
 *     "y2": 1060
 *   }
 * 
 * ##########################################
 * 
 * 1920x1080机型：
 *   "press_coefficient": 1.392,
 *   "piece_base_height_1_2": 20,
 *   "piece_body_width": 70,
 *   "swipe" : {
 *     "x1": 500,
 *     "y1": 1600,
 *     "x2": 500,
 *     "y2": 1602
 *   }
 * 
 * ##########################################
 *
 * 2160x1080机型：
 *   "press_coefficient": 1.372, 
 *   "piece_base_height_1_2": 25,
 *   "piece_body_width": 85
 *   "swipe" : {
 *     "x1": 320,
 *     "y1": 410,
 *     "x2": 320,
 *     "y2": 410
 *   }
 * 
 * 
 * ##########################################
 * 
 * 2560x1440机型：
 *   "press_coefficient": 1.035,
 *   "piece_base_height_1_2": 28,
 *   "piece_body_width": 110,
 *   "swipe" : {
 *     "x1": 320,
 *     "y1": 410,
 *     "x2": 320,
 *     "y2": 410
 *   }
 * 
 * ##########################################
 * 
 * 960x540机型：
 *   "press_coefficient": 2.732, 
 *   "piece_base_height_1_2": 20, 
 *   "piece_body_width": 70
 * 
 * ##########################################
 * 
 * honor_note8:
 *   "press_coefficient": 1.04,
 *   "piece_base_height_1_2": 90,
 *   "piece_body_width": 120,
 *   "swipe": {
 *       "x1": 730,
 *       "y1": 2100,
 *       "x2": 720,
 *       "y2": 2100
 *   }
 * 
 * ##########################################
 * 
 * mi_max2:
 *   "press_coefficient": 1.5,
 *   "piece_base_height_1_2": 20,
 *   "piece_body_width": 70,
 *   "swipe": {
 *       "x1": 500,
 *       "y1": 1600,
 *       "x2": 500,
 *       "y2": 1600
 *   }
 * 
 * ###########################################
 * 
 * mi5_mi5s:
 *   "press_coefficient": 1.475,
 *   "piece_base_height_1_2": 20,
 *   "piece_body_width": 70,
 *   "swipe": {
 *       "x1": 540,
 *       "y1": 1514,
 *       "x2": 540,
 *       "y2": 1514
 *   }
 * 
 * ###########################################
 * 
 * mi5x:
 *   "press_coefficient": 1.45,
 *   "piece_base_height_1_2": 25,
 *   "piece_body_width": 80,
 *   "swipe": {
 *       "x1": 560,
 *       "y1": 1550,
 *       "x2": 560,
 *       "y2": 1550
 *   }
 * 
 * ###########################################
 * 
 * mi6:
 *   "press_coefficient": 1.44,
 *   "piece_base_height_1_2": 20,
 *   "piece_body_width": 70,
 *   "swipe": {
 *       "x1": 500,
 *       "y1": 1600,
 *       "x2": 500,
 *       "y2": 1600
 *   }
 * 
 * ############################################
 * 
 * mi_note2:
 *   "press_coefficient": 1.47,
 *   "piece_base_height_1_2": 25,
 *   "piece_body_width": 80,
 *   "swipe": {
 *       "x1": 540,
 *       "y1": 1600,
 *       "x2": 540,
 *       "y2": 1600
 *   }
 * 
 * ############################################
 * 
 * samsung_s7edge:
 *   "press_coefficient": 1,
 *   "piece_base_height_1_2": 95,
 *   "piece_body_width": 102
 * 
 * ############################################
 * 
 * samsung_s8（在设置里关闭曲面侧屏）:
 *   "press_coefficient": 1.365,
 *   "piece_base_height_1_2": 70,
 *   "piece_body_width": 75
 * 
 * ############################################
 * 
 * smartisan_pro2:
 *  "press_coefficient": 1.392,
 *   "piece_base_height_1_2": 20,
 *   "piece_body_width": 70,
 *   "swipe" : {
 *     "x1": 320,
 *     "y1": 410,
 *     "x2": 320,
 *     "y2": 410
 *   }
 * 
 * 
 * 
 * 
 */


/**
 * 利用adb命令 拉取图片
 * @return [type] [description]
 */
function screenshot()
{
    @exec('adb shell screencap -p /sdcard/screen.png');
	@exec('adb pull /sdcard/screen.png .');
}

/**
 * 模拟按压跳一跳
 * @param [type] $distance
 * @return void
 */
function pressToJump($distance) {

	$press_time = $distance * PRESS_COEFFICIENT;
	$press_time = max($press_time, 200);//设置 200 ms 是最小的按压时间
	$press_time = intval($press_time);
	//用随机数尝试绕过VAC
	$press_cmd = 'adb shell input swipe ' . mt_rand(500, 520) . ' ' . mt_rand(1600, 1620) . ' ' . mt_rand(500, 520) . ' ' .mt_rand(1600, 1621) . ' ' . $press_time;//rand(参数值，参数值+20) 。初始位置按压的位置 500 1600 500 1601（这个位置对应的机型会自动重复开始游戏）
	echo sprintf("time: %f\n", $press_time);

	system($press_cmd);
}

/**
 * 算棋子的位置
 *
 * @return void
 */
function findStart() {
	global $image;
    
	$piece_y_max = 0;
    $piece_x_sum = 0;
    $piece_x_count = 0;
	$width  = imagesx($image);
	$height = imagesy($image);
	$piece_base_height_1_2 = PIECE_BASE_HEIGHT_1_2;//二分之一的棋子底座高度，请自己根据实际情况调节
	$scan_x_border = intval($width / 8);//扫描棋子时的左右边界，默认为宽度的 1/8
	$beginY = intval($height / 3);
	$endY = intval(($height / 3) * 2);
	$beginX = intval($scan_x_border);
	$endX = intval($width - $scan_x_border);
	
	//棋子应位于屏幕上半部分，不超过屏幕的2/3
	for ($y = $beginY; $y < $endY; $y++) {

		for ($x = $beginX; $x < $endX; $x++) {

			$pixel = getRGB($x, $y);
			// 根据棋子的最低行的颜色判断，找最后一行那些点的平均值，这个颜色这样应该 OK，暂时不提出来
            // 棋子的rgb在（50,53,90）---（60,63,110）
			if (($pixel[0] > 50 && $pixel[0] < 60) && ($pixel[1] > 53 && $pixel[1] < 63) && ($pixel[2] > 90 && $pixel[2] < 110)) {
				$piece_x_sum += $x;
				$piece_x_count += 1;
				$piece_y_max = max($y, $piece_y_max);
			}

		}
	}
	if($piece_x_count == 0) exit('Please open the game page first and configure swipe correctly!');
	$piece_x = intval($piece_x_sum / $piece_x_count);//棋子的底部上一点的位置是棋子最宽的位置，取平均值即为中点
	$piece_y = $piece_y_max - $piece_base_height_1_2; //上移棋子底盘高度的一半

	return [$piece_x, $piece_y];
}

/**
 * 算目标的位置
 * 算法有待改进
 * @return void
 */
function findTarget() {
	global $image;
	global $piece_x;
	global $piece_y;

	$width  = imagesx($image);
	$height = imagesy($image);
	$beginY = intval($height / 3);
	$endY = intval(($height / 3) * 2);
	//限制棋盘扫描的横坐标，避免音符bug
	if ($piece_x < $width / 2) {
		$beginX = $piece_x;
		$endX = $width-1;//防止溢出边界，还是减1吧
	}else {
		$beginX = 0;
		$endX = $piece_x;
	}
	$board_x = 0;
	$board_y = 0;

	$bg_pixel = getRGB($piece_x, $beginY);//棋子上方的这个点永远是背景点
	$board_pixel = null;//用于记录目标方块像素
	$board_x_sum = 0;
	$board_x_count = 0;
	$touchFlag = false;//判断是否扫描到方块，用于$touchTargetNum计数
	$touchTargetNum = 0;//扫描到目标的计算器
	$touch_y_first = 0;//用于记录目标方块顶点的y坐标
	$touch_y_last = 0;//用于记录目标方块表面底点的y坐标

	//找$board_x
	for ($y = $beginY; $y < $endY; $y++) {
		//记录方块顶点的y坐标
		if ($touchTargetNum == 1) {
			$touch_y_first = $y;
		}
		//扫描目标方块顶点以下两层像素就够了，减少误差
		if ($touchTargetNum == 2) {
			break;
		}
		if ($board_x || $board_y) {
			break;
		}
		
		for ($x = $endX; $x > $beginX; $x--) {//从右向左扫，避免阴影干扰

			$pixel = getRGB($x, $y);

			//修掉脑袋比下一个小格子还高的情况的 bug
			if (abs($x - $piece_x) < PIECE_BODY_WIDTH) {
				continue;
			}

			if (abs($pixel[0] - $bg_pixel[0]) + abs($pixel[1] - $bg_pixel[1]) + abs($pixel[2] - $bg_pixel[2]) < 20) {
				//20这个数很重要，10太小了
				//跳过背景色
				continue;
			}elseif (($pixel[0] > 50 && $pixel[0] < 60) && ($pixel[1] > 53 && $pixel[1] < 63) && ($pixel[2] > 90 && $pixel[2] < 110)) {
				// 棋子的rgb在（50,53,90）---（60,63,110）
				// 跳过棋子
				continue;
			}else {
				//此时扫描到目标方块
				$touchFlag = true;
				$board_pixel = $pixel;
				$board_x_sum += $x;
				$board_x_count += 1;
			}

		}
		if ($touchFlag) {
			$touchTargetNum++;
		}
		
	}
    
	if ($board_x_sum) {
		$board_x = intval($board_x_sum / $board_x_count);
	}

	//找$board_y
	//从上顶点往下上个方块中心y坐标的位置开始向上找颜色与上顶点一样的点，作为下顶点
	//该方法对所有纯色平面和部分非纯色平面有效，对高尔夫草坪面、木纹桌面、药瓶和非菱形的碟机（好像是）会判断错误
	
	for ($y = $piece_y; $y > $touch_y_first; $y--) {
		$pixel = getRGB($board_x, $y);
		if (abs($pixel[0] - $board_pixel[0]) + abs($pixel[1] - $board_pixel[1]) + abs($pixel[2] - $board_pixel[2]) < 10) {
			$touch_y_last = $y;
			break;
		}
	}

	$board_y = intval(($touch_y_first + $touch_y_last) / 2);

	//如果$touch_y_last-$touch_y_first < 棋子宽度，则 $board_y = $touch_y_first + 棋子宽度/2
	//这里选择棋子宽度/2是对没有白色中心点的音乐盒友好，对于没有白色中心点的小立体来说，可能有点大，可以根据情况调为棋子宽度/3（一般只要棋子宽度配置好了就行，这个一般不用调）
	//这样降低以上算法的失败概率
	if ($touch_y_last - $touch_y_first < PIECE_BODY_WIDTH) {
		$board_y = $touch_y_first + intval(PIECE_BODY_WIDTH / 2);
	}

	//如果上一跳命中中间，则下个目标中心会出现r245 g245 b245的点，利用这个属性弥补上一段代码可能存在的判断错误
    //若上一跳由于某种原因没有跳到正中间，而下一跳恰好有无法正确识别花纹，则有可能游戏失败，由于花纹面积通常比较大，失败概率较低
	for ($y = $touch_y_first; $y < $touch_y_first + 200; $y++) {
		$pixel = getRGB($board_x, $y);
		if (abs($pixel[0] - 245) + abs($pixel[1] - 245) + abs($pixel[2] - 245) == 0) {
			$board_y = $y + 10;
			break;
		}
	}

	//imagefilledellipse着色上顶点和下顶点，用于测试
	//imagefilledellipse($image, $board_x, $touch_y_first, 10, 10, 0x000000);
	//imagefilledellipse($image, $board_x, $touch_y_last, 10, 10, 0x0000FF);

	return [$board_x, $board_y];

}

/**
 * 获取像素点的rgb值
 *
 * @param [type] $x
 * @param [type] $y
 * @return void
 */
function getRGB($x, $y) {
	global $image;
	$rgba = imagecolorat($image, $x, $y);
	$r = ($rgba >> 16) & 0xFF;
	$g = ($rgba >> 8) & 0xFF;
	$b = $rgba & 0xFF;
	return [$r, $g, $b];
}


//提示：配置好相应机型的相应参数后，打开微信跳一跳界面，运行本脚本，就会自动重复开始游戏，想要结束得Ctrl+C
//如果配置开始按压的初始坐标swipe和相应的机型不对应的话，要手动开始游戏，再运行本脚本，并且游戏结束的时候，本脚本就会出错而自动结束运行。如果没有结束而是一直报错，请Ctrl+C手动结束
for ($id = 0; ; $id++) {
    echo sprintf("#%05d: ", $id);
    // 截图
	screenshot();
    // 获取坐标
	$image = imagecreatefrompng('screen.png');
	list($piece_x, $piece_y) = findStart();
	list($board_x, $board_y) = findTarget();
    if ($piece_x == 0) break;
	echo sprintf("(%d, %d) -> (%d, %d) ", $piece_x, $piece_y, $board_x, $board_y);

    //imagefilledellipse图像描点,用于测试（算坐标的算法）,要在当前目录新建一个名为screen的文件夹
	//imagefilledellipse($image, $piece_x, $piece_y, 10, 10, 0xFF0000);
	//imagefilledellipse($image, $board_x, $board_y, 10, 10, 0xFF0000);
	//imagepng($image, sprintf("screen/%05d.png", $id));

    // 计算两点距离并跳一跳
	$distance = sqrt(pow($board_x - $piece_x, 2) + pow($board_y - $piece_y, 2));
	pressToJump($distance);
    // 等待下一次截图
	sleep(SLEEP_TIME);
}
