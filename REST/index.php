<?php

define('ROOT', '../ClassRoom/');
define('FILE_EXT', '(\..*)');
define('SIZE', 128);

$method	 = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$path	 = filter_input(INPUT_SERVER, 'PATH_INFO');
$request = explode('/', trim($path, '/'));

//$link = mysqli_connect('localhost', 'id6080538_root', '12345678', 'DB');
//mysqli_set_charset($link, 'utf8');

switch ($method)
{
	case 'GET':

		$args = array_slice($request, 1);

		switch ($request[0])
		{
			case 'LIST':

				$directory = ROOT;

				if (count($args) == 0)
				{
					$listing = array();
					foreach (array_diff(scandir($directory), array('..', '.')) as $entry)
						if (is_dir($directory . $entry))
							array_push($listing, $entry);

					header('Content-type: application/json; charset=utf-8');
					echo(json_encode(array_values($listing), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
					exit();
				}

				$directory .= join(DIRECTORY_SEPARATOR, array_map('utf8_decode', $args));

				if (is_dir($directory))
				{
					$listing = preg_filter('$(.*)' . FILE_EXT . '$i', '$1', array_map('utf8_encode', array_diff(scandir($directory), array('..', '.'))));

					header('Content-type: application/json; charset=utf-8');
					echo(json_encode(array_values($listing), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
					exit();
				}
				else
				{
					http_response_code(404);
					exit();
				}

			case 'IMG':

				$pattern = ROOT . join(DIRECTORY_SEPARATOR, array_map('utf8_decode', $args)) . '*';
				$files	 = glob($pattern);

				if (count($files) >= 1)
				{
					$file = $files[0];

					if (is_file($file))
					{
						$old = imagecreatefromstring(file_get_contents($file));
						$new = imagecreatetruecolor(SIZE, SIZE);
						imagecopyresampled($new, $old, 0, 0, 0, 0, SIZE, SIZE, imagesx($old), imagesy($old));

						header('Content-type: image/png');
						imagepng($new);
						exit();
					}
					else
					{
						http_response_code(404);
						exit();
					}
				}
		}
}
