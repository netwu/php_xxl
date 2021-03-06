<?php
define('rand_str', ['🍓', '🍏', '🍎', '🍋']);
define('bomb', '💣');
define('sleep_time', 1);

function createGrid()
{
	$grid = [];
	for ($i = 0; $i < columns; $i++) {
		$rows_grid = [];
		for ($j = 0; $j < rows; $j++) {
			if ($i == 0 && $j == 0) {
				$rows_grid[] = '';
			} elseif ($i > 0 && $j == 0) {
				$rows_grid[] = $i;
			} elseif ($i == 0 && $j > 0) {
				$rows_grid[] = $j;
			} else {
				$rows_grid[] = getRandStr();
			}
		}
		$grid[] = $rows_grid;
	}
	return $grid;
}

function getRandStr()
{
	return rand_str[array_rand(rand_str)];
}

function printGrid($grid, $msg, $is_sleep = 1)
{
	$pre_str = '     ';
	$max_len = max(strlen(columns), 2);

	system('clear');
	echo "playing_____{$msg}", PHP_EOL;
	for ($i = 0; $i < columns; $i++) {
		for ($j = 0; $j < rows; $j++) {
			$val = $grid[$i][$j];
			if ($i == 0 || $j == 0) {
				$val = substr($pre_str . $val, -$max_len);
			} else {
				$val = substr($pre_str . $val, -4 - ($max_len - 2));
			}
			echo $val, ' ';
		}
		echo PHP_EOL;
	}
	if ($is_sleep) {
		sleep(sleep_time);
	}
}

function detectGrid($grid)
{
	$disappersgrid = [];
	for ($i = 1; $i < columns; $i++) {
		$columns_grid = $grid[$i];
		for ($j = 1; $j < rows; $j++) {
			if (
				(isset($columns_grid[$j - 1]) && $columns_grid[$j - 1] == $columns_grid[$j]) && (isset($columns_grid[$j + 1]) && $columns_grid[$j + 1] == $columns_grid[$j])
			) {
				$disappersgrid[$j][$i] = '';
				$disappersgrid[$j - 1][$i] = '';
				$disappersgrid[$j + 1][$i] = '';
			}

			if (
				(isset($grid[$i - 1]) && $grid[$i - 1][$j] == $columns_grid[$j]) && (isset($grid[$i + 1]) && $grid[$i + 1][$j] == $columns_grid[$j])
			) {
				$disappersgrid[$j][$i] = '';
				$disappersgrid[$j][$i - 1] = '';
				$disappersgrid[$j][$i + 1] = '';
			}
		}
	}
	return $disappersgrid;
}
function redisplayGrid(&$grid, $disappersgrid, $str = '')
{
	foreach ($disappersgrid as $column_index => $row_index) {
		foreach ($row_index as $c => $notuse) {
			$grid[$c][$column_index] = $str == 'rand' ? getRandStr() : $str;
		}
	}
}

function reStartGrid(&$grid, $disappersgrid)
{
	foreach ($disappersgrid as $column_index => $row_indexs) {
		krsort($row_indexs);
		$keys = array_keys($row_indexs);
		$have_value_arr = [];

		for ($i = $keys[0] - 1; $i >= 1; $i--) {
			if (!in_array($i, $keys)) {
				$have_value_arr[] = $grid[$i][$column_index];
			}
		}
		for ($j = $keys[0]; $j >= 1; $j--) {
			$tmp_val = array_shift($have_value_arr);
			if ($tmp_val) {
				$grid[$j][$column_index] = $tmp_val;
			} else {
				$grid[$j][$column_index] = getRandStr();
			}
		}
	}
}

function startGame()
{
	if (!defined('columns')) {
		fwrite(STDOUT, "输入列数(大于等于3)：");
		$column = trim(fgets(STDIN));
		if (!is_numeric($column) || $column < 3) {
			fwrite(STDOUT, '输入不合法,重新输入' . PHP_EOL);
			startGame();
		}
		define('columns', $column + 1);
	}
	if (!defined('rows')) {
		fwrite(STDOUT, "输入行数(大于等于3)：");
		$row = trim(fgets(STDIN));
		if (!is_numeric($row) || $row < 3) {
			fwrite(STDOUT, '输入不合法,重新输入' . PHP_EOL);
			startGame();
		}
		define('rows', $row + 1);
	}

	$grid = createGrid();
	$score = 0;
	printGrid($grid, 'score:' . $score, 1);
	while (true) {
		$disappersgrid = detectGrid($grid);
		if (empty($disappersgrid)) {
			printGrid($grid, 'score:' . $score, 0);
			fwrite(STDOUT, "输入两个交换坐标（行1,列1>行2,列2）：");
			$msg = trim(fgets(STDIN));
			$coordinates = explode('>', $msg);
			if (empty($coordinates) || count($coordinates) != 2) {
				printGrid($grid, '输入不合法,重新输入');
			} else {
				$c1 = explode(',', $coordinates[0]);
				$c2 = explode(',', $coordinates[1]);

				if (
					isset($grid[$c1[0]][$c1[1]]) && isset($grid[$c2[0]][$c2[1]])
				) {
					$tmp = $grid[$c1[0]][$c1[1]];
					$grid[$c1[0]][$c1[1]] = $grid[$c2[0]][$c2[1]];
					$grid[$c2[0]][$c2[1]] = $tmp;
				} else {
					printGrid($grid, '输入不合法,重新输入');
				}
			}
		} else {
			$score++;
		}
		redisplayGrid($grid, $disappersgrid, bomb);
		printGrid($grid, 'score:' . $score, 1);
		reStartGrid($grid, $disappersgrid);
		printGrid($grid, 'score:' . $score);
	}
}

startGame();
