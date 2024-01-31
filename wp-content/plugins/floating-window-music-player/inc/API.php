<?php
if (!defined('ABSPATH')) exit;
function Fwm_Player_footer() {
  if (isMobile() && get_option('Fwm_player_move') == 'NO') return 0;
  echo '<script>name = "'. get_option('Fwm_player_name'). '"; tips = "'. get_option('Fwm_player_tips'). '"; qqid = "'. get_option('Fwm_player_qqid'). '"; ajax_url = "'. admin_url('admin-ajax.php'). '"; move = "'. get_option('Fwm_player_move'). '"; geci = "'. get_option('Fwm_player_geci'). '"; auto = "'. get_option('Fwm_player_auto'). '"; suiji = "'. get_option('Fwm_player_suiji'). '"; random = "'. get_option('Fwm_player_random'). '"; FwmURL = "'. Fwm_Player_URL. '"; ver = "'. Fwm_Player_VER. '"; welcome = "open";st = "'. intval(get_option('Fwm_player_st'))*1000 . '";</script>';
  if (!is_admin() && !is_null(get_option('Fwm_player_css'))) echo '<style type = "text/css">' . get_option('Fwm_player_css') . '</style>';
  wp_enqueue_script('mCustomScrollbar', 'https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/2.8.5/jquery.mCustomScrollbar.min.js', '', '2.8.5');
  wp_enqueue_script('mousewheel', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.9/jquery.mousewheel.min.js', '', '3.1.9');
  wp_enqueue_style('Fwmplayer', Fwm_Player_URL. '/inc/player.css', '', Fwm_Player_VER);
  wp_enqueue_script('Fwmplayer', Fwm_Player_URL. '/js/load.js', '', Fwm_Player_VER);
  wp_localize_script('Fwmplayer', 'pollsL10n', array(
    'Time' => __('Time mode', FwmTD),
    'Playback' => __('Play mode', FwmTD),
    'Lyric' => __('Lyrics mode', FwmTD),
    'Previous' => __('Previous album', FwmTD),
    'Pre' => __('Previous song', FwmTD),
    'Playb' => __('Play', FwmTD),
    'Next' => __('Next album', FwmTD),
    'Nexts' => __('Next song', FwmTD),
    'SP' => __('Song progress', FwmTD),
    'Homepage' => __('Homepage of Music Player', FwmTD),
    'Down' => __('Download', FwmTD),
    'View' => __('View song cover', FwmTD),
    'close' => __('Lyric:Close', FwmTD),
    'open' => __('Lyric:Origin', FwmTD),
    'trans' => __('Lyric:Trans', FwmTD),
    'Shuffle' => __('Shuffle', FwmTD),
    'Order' => __('Dir cycle', FwmTD),
    'Single' => __('On repeat', FwmTD),	
	'Random' => __('All random', FwmTD),
    'volume' => __('Volume', FwmTD),
    'song' => __('Song：', FwmTD),
    'singer' => __('Singer：', FwmTD),
    'album' => __('Album：', FwmTD),
    'hide' => __('Hide the player', FwmTD),
    'show' => __('Expand the player', FwmTD),
    'mute' => __('Mute', FwmTD),
    'download' => __('Downoad ', FwmTD),
    'play' => __('Current Playing', FwmTD),
    'Playlist' => __('Playlist', FwmTD),
    'Failed' => __(' Get Failed', FwmTD),
    'success' => __(' Get Success', FwmTD),
    'Autoplay' => __('Autoplay', FwmTD),
    'Stop' => __('Stop playing', FwmTD),
	'three' => __('Playback failed more than 3 times in a row. ', FwmTD),
    'Auto' => __('Auto ', FwmTD),
    'off' => __('Turn off ', FwmTD),
    'SNext' => __('Stop playing Next time', FwmTD),
    'ANext' => __('Auto playing Next time', FwmTD),
    'from' => __('Playing from ', FwmTD),
    'Pause' => __('Pause', FwmTD),
    'Nol' => __('No lyric', FwmTD),
    'List' => __(' Song Lists from ', FwmTD),
    'Songs' => __(' Songs ', FwmTD),
	'albums' => __(' albums', FwmTD),
    'Seek' => __('Song Seeking', FwmTD),
    'Load' => __('Loading songs', FwmTD),
    'wait' => __('Please wait..', FwmTD),
    'lyrics' => __('Lyric:', FwmTD)
  ));
}
add_action('wp_ajax_Fwm_api', 'Fwm_api');
add_action('wp_ajax_nopriv_Fwm_api', 'Fwm_api');
function Fwm_api() {
  if (!class_exists('Meting')) require_once('Meting.php');
  $id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
  $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
  switch (sanitize_text_field($_GET['do'])) {
    case 'color':
      $url = str_replace("https", "http", sanitize_text_field($_GET['url']));
      $imageInfo = getimagesize($url);
      $imgType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
      $imageFun = 'imagecreatefrom' . ($imgType == 'jpg' ? 'jpeg' : $imgType);
      $i = $imageFun($url);
      $rColorNum = $gColorNum = $bColorNum = 0;
      for ($x = 0; $x < imagesx($i); $x++) {
        for ($y = 0; $y < imagesy($i); $y++) {
          $rgb = imagecolorat($i, $x, $y);
          $rColorNum += $rgb >> 16 & 0xff;
          $gColorNum += $rgb >> 8 & 0xff;
          $bColorNum += $rgb & 0xff;
        }
      }
      $rgb = array();
      $rgb['r'] = round($rColorNum / ($x*$y));
      $rgb['g'] = round($gColorNum / ($x*$y));
      $rgb['b'] = round($bColorNum / ($x*$y));
      echo "var ccont = '". $rgb['r']. ",". $rgb['g']. ",". $rgb['b']. "';";
      $R = (abs(255 - $rgb['r']*2) < 100) ? abs($rgb['r'] - 88) : (255 - $rgb['r']);
      $G = (abs(255 - $rgb['g']*2) < 100) ? abs($rgb['g'] - 88) : (255 - $rgb['g']);
      $B = (abs(255 - $rgb['b']*2) < 100) ? abs($rgb['b'] - 88) : (255 - $rgb['b']);
      echo "var ccont1 = '". $R. ",". $G. ",". $B. "';";
      exit;
    case 'song':
      echo "var FwmLists = [";
        $songlist = explode('|', $id);
        $c = count($songlist);
		$userplaylist = array();
        !$songlist[$c-1] && $c -= 1;
        for ($i = 0; $i < $c; $i++) {
          $songid = explode('*', $songlist[$i]);
		  $songid[0] = mb_convert_encoding($songid[0], 'utf-8', mb_detect_encoding($songid[0], array('UTF-8', 'GBK', 'LATIN1', 'BIG5')));
		  if ($songid[3] == 'netease' & $songid[2] == '0') {
			$d = 0;
		    $response = json_decode(Fwm_Player_get_curl("http://music.163.com/api/user/playlist/?offset=0&limit=1001&uid=". $songid[1]), 1);
		    foreach ($response['playlist'] as $tracks) {
		      $songs[$tracks['id']] = Fwm_playlist($tracks['id'], $songid[3]);
		      $userplaylist[] = array("song_album_id" => $tracks['id'], "song_album" => $tracks['name'], "song_album1" => __('Playing from ', FwmTD). __('Netease Music', FwmTD), "song_list" => "", "song_type" => $songid[3]);
			  $d += 1;
		    }
		    unset($userplaylist[$i]);
			$d -= 2;
			continue;
		  }
          $songid[2] == '1' && $songs[$songid[0]] = Fwm_album($songid[1], $songid[3]);
          $songid[2] == '2' && $songs[$songid[0]] = Fwm_Player_search($songid[0], $songid[1], $songid[3]);
          $songid[2] == '3' && $songs[$songid[0]] = Fwm_playlist($songid[1], $songid[3]);
		  $songid[3] == 'netease' && $s = __('Netease Music', FwmTD);
		  $songid[3] == 'tencent' && $s = __('QQ Music', FwmTD);
		  $songid[3] == 'baidu' && $s = __('Baidu Music', FwmTD);
		  $songid[3] == 'xiami' && $s = __('Xiami Music', FwmTD);
		  $songid[3] == 'kugou' && $s = __('Kugou Music', FwmTD);
		  $songid[3] == 'kuwo' && $s = __('Kuwo Music', FwmTD);
          $userplaylist[] = array("song_album_id" => $songid[0], "song_album" => $songid[0], "song_album1" => __('Playing from ', FwmTD). $s, "song_list" => "", "song_type" => $songid[3]);
        }
		$c += $d;
        get_option('Fwm_player_suiji') == "YES" && shuffle($userplaylist);
        for ($i = 0; $i < $c; $i++) {
          $song_lists = array();
          foreach ($songs[$userplaylist[$i]['song_album_id']] as $tracks) {$song_lists[] = array("song_id" => $tracks['id'], "song_title" => $tracks['name'], "singer" => $tracks['artist']['0'], "album" => $tracks['album'], "pic" => Fwm_pic($tracks['pic_id'], $userplaylist[$i]['song_type']), "mp3url" => admin_url('admin-ajax.php') . '?action=Fwm_api&do=url&type=' . $userplaylist[$i]['song_type'] . '&id=' . $tracks['id']);}
          $userplaylist[$i]['song_list'] = $song_lists;
          print_r(json_encode($userplaylist[$i]));
          echo $i < $c - 1 ? "," : "";
        }
      echo "]";
      exit;
    case 'lyric':
      $API = new \Metowolf\Meting($type);
      $data = json_decode(str_replace(array('\\r\\n', '\\r', '\\n', '\"'), '', $API->format(1)->lyric($id)), 1);
      echo 'var cont = "[00:00.01]~'. __('Key: Play / Pause ', FwmTD). get_option('Fwm_player_tips'). $data['lyric']. '"; ';
      echo 'var contt = "[00:00.01]~'. __('Key: Play / Pause ', FwmTD). get_option('Fwm_player_tips'). $data['tlyric']. '";';
      exit;
    case 'url':
      $API = new \Metowolf\Meting($type);
      $data = json_decode($API->format(1)->url($id), 1);
      if ($type == 'netease') {
		$data['url'] = str_replace("http://m7c", "https://m7", $data['url']);
		$data['url'] = str_replace("http://m8c", "https://m8", $data['url']);
		$data['url'] = str_replace("http://", "https://", $data['url']);
      }
      if ($type == 'baidu') $data['url'] = str_replace("http://zhangmenshiting.qianqian.com", "https://gss0.bdstatic.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu", $data['url']);
	  if ($type == 'tencent') $data['url'] = str_replace("http://ws.stream.qqmusic.qq.com/", "https://dl.stream.qqmusic.qq.com/", $data['url']);	  
      Header("Location:". $data['url']); 
      exit;
  }
  wp_die();
}
function Fwm_Player_get_curl($url) { 
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Connection:close"));
  curl_setopt($ch, CURLOPT_ENCODING, "gzip");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $ret = curl_exec($ch);
  curl_close($ch);
  return $ret;
}
function isMobile() {
  if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) return 1;
  if (isset ($_SERVER['HTTP_VIA'])) return stristr($_SERVER['HTTP_VIA'], "wap") ? 1 : 0;
  if (isset ($_SERVER['HTTP_USER_AGENT'])) {
    $clientkeywords = array ('mobile', 'iPhone', 'iPod', 'Android', 'ios', 'ipad', 'ucweb', 'iphone', 'ipod', 'iPad');
    if (preg_match("/(". implode('|', $clientkeywords). ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) return 1;
  }
  return 0;
}
function Fwm_Player_search($keyword, $limit, $type) {
  $API = new \Metowolf\Meting($type);
  $data = json_decode($API -> format(1) -> search($keyword, array('page' => 1, 'limit' => $limit)), 1);
  return $data; 
}
function Fwm_playlist($id, $type) {
  $API = new \Metowolf\Meting($type);
  if ($type == 'netease') $API -> cookie(get_option('Fwm_player_cookie'));
  $data = json_decode($API -> format(1) -> playlist($id),1);
  return $data;
}
function Fwm_album($id, $type) {
  $API = new \Metowolf\Meting($type);
  $data = json_decode($API -> format(1) -> album($id),1);
  return $data;
}
function Fwm_pic($id, $type) {
  $API = new \Metowolf\Meting($type);
  $data = json_decode($API -> format(1) -> pic($id), 1);
  return $data['url']; 
}
?>