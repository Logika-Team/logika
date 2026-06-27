<?php
	$directory = __DIR__;

	$htmlFiles = glob($directory . "/*.html");
	function generateNavigation($files) {
		$output = '<ul>';
		foreach ($files as $file) {
			$fileName = basename($file);
			$fileTitle = ucwords(str_replace(array('-', '_', '.html'), array(' ', ' ', ''), $fileName));
			$output .= '<li><a href="' . $fileName . '">' . $fileTitle . '</a></li>';
		}
		$output .= '</ul>';
		return $output;
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>HTML Files Navigation</title>
	<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
        }
        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #007BFF;
        }
        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        li {
            margin: 10px 0;
            border-bottom: 1px solid #eee;
        }
        li:last-child {
            border-bottom: none;
        }
        a {
            text-decoration: none;
            color: #007BFF;
            font-size: 18px;
            display: block;
            padding: 10px;
            transition: background-color 0.3s, color 0.3s;
        }
        a:hover {
            background-color: #f0f8ff;
            color: #0056b3;
            border-radius: 4px;
        }
	</style>
</head>
<body>
<div class="container">
	<h1>HTML Files Navigation</h1>
	<?php
		if (!empty($htmlFiles)) {
			echo generateNavigation($htmlFiles);
		} else {
			echo '<p>No HTML files found in the directory.</p>';
		}
	?>
</div>
</body>
</html>
