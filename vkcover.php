<?php
/**
 * @author Denis Utkin <dizirator@gmail.com>
 * @link   https://github.com/dizirator
 */
		
$params = [
	'******' => [ // group ID
		'groupID'   => '******', // group ID
		'token'     => '******',
		'imagePath' => '/images'
	],
];

$gid = isset($_GET['gid']) ? $_GET['gid'] : null;

// ---------------------

	
if (isset($params[$gid])) {
	$params = [$params[$gid]];
}

setCoverProcess($params);

// ---------------------

function getImage($path)
{
	$baseDir = $_SERVER['DOCUMENT_ROOT'];
	$files   = [];
	foreach (glob($baseDir . $path . '/*.{jpg,png,gif}', GLOB_BRACE) as $file) {
		$files[] = $file;
	}	
	return $files[mt_rand(0, count($files) - 1)];
}

function uploadImage($url, $path) 
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	if (class_exists('\CURLFile')) {
	    curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo' => new \CURLFile($path)]);
	} else {
	    curl_setopt($ch, CURLOPT_POSTFIELDS, ['photo' => "@$path"]);
	}
	$result = json_decode(curl_exec($ch), true);
	curl_close($ch);
	return $result;
}

function setCoverProcess($params, $showResult = false)
{
	if (!is_array($params)) return;
	 
	foreach ($params as $item) {
		$uploadUrlResult = file_get_contents("https://api.vk.com/method/photos.getOwnerCoverPhotoUploadServer?group_id={$item['groupID']}&crop_x2=1590&v=5.80&access_token={$item['token']}");
		$uploadUrl       = json_decode($uploadUrlResult, true)['response']['upload_url'];
		$image           = getImage($item['imagePath']);
		$uoloadResult    = uploadImage($uploadUrl, $image);
		$saveResult      = file_get_contents("https://api.vk.com/method/photos.saveOwnerCoverPhoto?hash={$uoloadResult['hash']}&photo={$uoloadResult['photo']}&v=5.80&access_token={$item['token']}");
		
		if ($showResult) {
			echo '<pre>';
			print_r($item);
			print_r($uploadUrlResult);
			print_r($uoloadResult);
            print_r($saveResult);
		}
	}
}