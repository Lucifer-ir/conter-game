<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

const MAX_BODY = 16384;
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $parsed = parse_url($origin);
    if (!$parsed || strtolower($parsed['host'] ?? '') !== strtolower(explode(':', $host)[0])) { respond(false, null, 'درخواست بین‌دامنه‌ای مجاز نیست.', 403); }
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { respond(false, null, 'فقط درخواست POST مجاز است.', 405); }
$raw = file_get_contents('php://input', false, null, 0, MAX_BODY + 1);
if (strlen($raw) > MAX_BODY) respond(false, null, 'درخواست بیش از حد بزرگ است.', 413);
$in = json_decode($raw ?: '{}', true);
if (!is_array($in)) respond(false, null, 'JSON نامعتبر است.', 400);
$action = (string)($in['action'] ?? '');
$dataDir = dirname(__DIR__) . '/data';
if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true) && !is_dir($dataDir)) respond(false,null,'ساخت پوشه داده ناموفق بود.',500);
$usersFile = $dataDir . '/users.json'; $roomsFile = $dataDir . '/rooms.json';
foreach ([$usersFile,$roomsFile] as $f) if (!file_exists($f)) file_put_contents($f, "{}", LOCK_EX);
function respond(bool $ok, $payload=null, string $error='', int $status=200): never { http_response_code($status); echo json_encode($ok ? ['ok'=>true]+(is_array($payload)?$payload:[]) : ['ok'=>false,'error'=>$error], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
function readStore(string $file): array { $fp=fopen($file,'c+'); if(!$fp) respond(false,null,'خواندن داده‌ها ممکن نیست.',500); flock($fp,LOCK_SH); $raw=stream_get_contents($fp); flock($fp,LOCK_UN); fclose($fp); $v=json_decode($raw ?: '{}',true); return is_array($v)?$v:[]; }
function writeStore(string $file,array $data): void { $fp=fopen($file,'c+'); if(!$fp) respond(false,null,'ذخیره داده‌ها ممکن نیست.',500); flock($fp,LOCK_EX); ftruncate($fp,0); rewind($fp); fwrite($fp,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)); fflush($fp); flock($fp,LOCK_UN); fclose($fp); }
function cleanUser(array $u): array { return ['id'=>$u['id'],'username'=>$u['username'],'display_name'=>$u['display_name']]; }
function currentUser(): array { if(empty($_SESSION['uid'])) respond(false,null,'ابتدا وارد حساب شوید.',401); $users=readStore(dirname(__DIR__).'/data/users.json'); $u=$users[$_SESSION['uid']]??null; if(!$u) respond(false,null,'حساب کاربری پیدا نشد.',401); return $u; }
function validMap($map): int { $m=filter_var($map,FILTER_VALIDATE_INT); if($m===false || $m<0 || $m>3) respond(false,null,'نقشه انتخاب‌شده معتبر نیست.',422); return $m; }
function roomPublic(array $r,string $uid): array { $members=[]; foreach(($r['members']??[]) as $id=>$m) $members[]=['id'=>$id,'username'=>$m['username'],'display_name'=>$m['display_name'],'is_host'=>$id===$r['host_id'],'online'=>(time()-($m['last_seen']??0)<25)]; $modeLabels=['coop'=>'همکاری','team'=>'تیمی','ffa'=>'همه علیه همه']; return ['code'=>$r['code'],'name'=>$r['name'],'map'=>$r['map'],'map_name'=>['Forest Outpost','Desert Town','Industrial Yard','Snow Base'][$r['map']],'mode'=>$r['mode'],'mode_label'=>$modeLabels[$r['mode']]??$r['mode'],'capacity'=>$r['capacity'],'members'=>$members,'count'=>count($r['members']??[]),'bot_count'=>(int)($r['bot_count']??0),'is_private'=>(bool)($r['is_private']??false),'is_host'=>$uid===$r['host_id'],'host_id'=>$r['host_id'],'status'=>$r['status']]; }
function getRoom(array &$rooms,string $code): array { $code=strtoupper(preg_replace('/[^A-Z0-9]/','',$code)); if(!isset($rooms[$code])) respond(false,null,'اتاق پیدا نشد یا منقضی شده است.',404); return $rooms[$code]; }
$uid=$_SESSION['uid']??'';
switch($action) {
case 'register':
  $username=strtolower(trim((string)($in['username']??''))); $password=(string)($in['password']??''); $display=trim((string)($in['display_name']??''));
  if(!preg_match('/^[a-z0-9_]{3,24}$/',$username)) respond(false,null,'نام کاربری باید ۳ تا ۲۴ حرف انگلیسی، عدد یا زیرخط باشد.',422);
  if(strlen($password)<8 || strlen($password)>200) respond(false,null,'رمز عبور باید حداقل ۸ کاراکتر باشد.',422);
  if($display==='') $display=$username; if(strlen($display)>84) respond(false,null,'نام نمایشی حداکثر ۲۸ کاراکتر باشد.',422);
  $users=readStore($usersFile); foreach($users as $u) if($u['username']===$username) respond(false,null,'این نام کاربری قبلاً ثبت شده است.',409);
  $id=bin2hex(random_bytes(12)); $users[$id]=['id'=>$id,'username'=>$username,'display_name'=>$display,'password_hash'=>password_hash($password,PASSWORD_DEFAULT),'created_at'=>time(),'last_seen'=>time()]; writeStore($usersFile,$users); session_regenerate_id(true); $_SESSION['uid']=$id; respond(true,['user'=>cleanUser($users[$id])]);
case 'login':
  $username=strtolower(trim((string)($in['username']??''))); $password=(string)($in['password']??''); $users=readStore($usersFile); $found=null; foreach($users as $u) if($u['username']===$username){$found=$u;break;} if(!$found || !password_verify($password,$found['password_hash'])) respond(false,null,'نام کاربری یا رمز عبور اشتباه است.',401); $found['last_seen']=time(); foreach($users as $key=>$item){if($key===$found['id']){$users[$key]=$found;break;}} writeStore($usersFile,$users); session_regenerate_id(true); $_SESSION['uid']=$found['id']; respond(true,['user'=>cleanUser($found)]);
case 'me': if(!$uid) respond(true,['user'=>null]); $users=readStore($usersFile); respond(true,['user'=>isset($users[$uid])?cleanUser($users[$uid]):null]);
case 'logout': $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); respond(true,['message'=>'خارج شدید.']);
case 'list_users':
  $u=currentUser(); $users=readStore($usersFile); if(isset($users[$u['id']])){$users[$u['id']]['last_seen']=time();writeStore($usersFile,$users);} $out=[]; foreach($users as $item){$out[]=['username'=>$item['username'],'display_name'=>$item['display_name'],'online'=>(time()-($item['last_seen']??0)<35)];} usort($out,fn($a,$b)=>strcmp($a['username'],$b['username'])); respond(true,['users'=>$out,'count'=>count($out)]);
case 'list_rooms':
  currentUser(); $rooms=readStore($roomsFile); $out=[]; $changed=false; foreach($rooms as $code=>$r){ foreach($r['members'] as $mid=>$m) if(time()-($m['last_seen']??0)>180){unset($rooms[$code]['members'][$mid]);$changed=true;} if(!$rooms[$code]['members']){unset($rooms[$code]);$changed=true;continue;} if($rooms[$code]['host_id'] && !isset($rooms[$code]['members'][$rooms[$code]['host_id']])){$rooms[$code]['host_id']=array_key_first($rooms[$code]['members']);$changed=true;} if($rooms[$code]['status']==='lobby' && empty($rooms[$code]['is_private']) && count($out)<50) $out[]=roomPublic($rooms[$code],$_SESSION['uid']); } if($changed)writeStore($roomsFile,$rooms); respond(true,['rooms'=>$out]);
case 'create_room':
  $u=currentUser(); $map=validMap($in['map']??0); $mode=(string)($in['mode']??'coop'); if(!in_array($mode,['coop','team','ffa'],true))respond(false,null,'حالت بازی نامعتبر است.',422); $cap=filter_var($in['capacity']??8,FILTER_VALIDATE_INT); if($cap===false||$cap<2||$cap>12)respond(false,null,'ظرفیت باید بین ۲ تا ۱۲ باشد.',422); $bots=filter_var($in['bot_count']??0,FILTER_VALIDATE_INT); if($bots===false||!in_array($bots,[0,2,4,6],true)||$bots>=$cap)respond(false,null,'تعداد بات‌ها نامعتبر است یا ظرفیت کافی نیست.',422); $private=filter_var($in['is_private']??false,FILTER_VALIDATE_BOOLEAN); $rooms=readStore($roomsFile); do{$code=strtoupper(substr(bin2hex(random_bytes(4)),0,6));}while(isset($rooms[$code])); $rooms[$code]=['code'=>$code,'name'=>$u['display_name'].' - Room','host_id'=>$u['id'],'map'=>$map,'mode'=>$mode,'capacity'=>$cap,'bot_count'=>$bots,'is_private'=>$private,'status'=>'lobby','created_at'=>time(),'members'=>[$u['id']=>['username'=>$u['username'],'display_name'=>$u['display_name'],'last_seen'=>time(),'position'=>['x'=>0,'y'=>0,'z'=>0,'yaw'=>0,'pitch'=>0,'hp'=>100,'kills'=>0,'alive'=>false]]]]; writeStore($roomsFile,$rooms); respond(true,['room'=>roomPublic($rooms[$code],$u['id'])]);
  $rooms=readStore($roomsFile); do{$code=strtoupper(substr(bin2hex(random_bytes(4)),0,6));}while(isset($rooms[$code])); $rooms[$code]=['code'=>$code,'name'=>$u['display_name'].' - Room','host_id'=>$u['id'],'map'=>$map,'mode'=>$mode,'capacity'=>$cap,'status'=>'lobby','created_at'=>time(),'members'=>[$u['id']=>['username'=>$u['username'],'display_name'=>$u['display_name'],'last_seen'=>time(),'position'=>['x'=>0,'y'=>0,'z'=>0,'yaw'=>0,'pitch'=>0,'hp'=>100,'kills'=>0,'alive'=>false]]]]; writeStore($roomsFile,$rooms); respond(true,['room'=>roomPublic($rooms[$code],$u['id'])]);
case 'join_room':
  $u=currentUser(); $rooms=readStore($roomsFile); $code=strtoupper(trim((string)($in['code']??''))); $r=getRoom($rooms,$code); if(!isset($r['members'][$u['id']]) && count($r['members']) + (int)($r['bot_count']??0) >= $r['capacity'])respond(false,null,'این اتاق ظرفیت خالی ندارد.',409); if($r['status']!=='lobby' && !isset($r['members'][$u['id']]))respond(false,null,'بازی شروع شده و ورود جدید بسته است.',409); $r['members'][$u['id']]=['username'=>$u['username'],'display_name'=>$u['display_name'],'last_seen'=>time(),'position'=>['x'=>0,'y'=>0,'z'=>0,'yaw'=>0,'pitch'=>0,'hp'=>100,'kills'=>0,'alive'=>false]]; $rooms[$code]=$r; writeStore($roomsFile,$rooms); respond(true,['room'=>roomPublic($r,$u['id'])]);
case 'update_room':
  $u=currentUser(); $rooms=readStore($roomsFile); $code=strtoupper(trim((string)($in['code']??''))); $r=getRoom($rooms,$code); if($r['host_id']!==$u['id'])respond(false,null,'فقط میزبان می‌تواند تنظیمات را تغییر دهد.',403); if($r['status']!=='lobby')respond(false,null,'تنظیمات بعد از شروع بازی قابل تغییر نیست.',409); $r['map']=validMap($in['map']??$r['map']); $mode=(string)($in['mode']??$r['mode']); if(!in_array($mode,['coop','team','ffa'],true))respond(false,null,'حالت بازی نامعتبر است.',422); $cap=filter_var($in['capacity']??$r['capacity'],FILTER_VALIDATE_INT); if($cap===false||$cap<2||$cap>12||$cap<count($r['members']))respond(false,null,'ظرفیت نامعتبر است یا از تعداد اعضا کمتر است.',422); $bots=filter_var($in['bot_count']??($r['bot_count']??0),FILTER_VALIDATE_INT); if($bots===false||!in_array($bots,[0,2,4,6],true)||$bots+count($r['members'])>$cap)respond(false,null,'تعداد بات‌ها با ظرفیت سرور سازگار نیست.',422); $r['mode']=$mode;$r['capacity']=$cap;$r['bot_count']=$bots;$r['is_private']=filter_var($in['is_private']??($r['is_private']??false),FILTER_VALIDATE_BOOLEAN);$rooms[$code]=$r;writeStore($roomsFile,$rooms);respond(true,['room'=>roomPublic($r,$u['id'])]);
case 'start_room':
  $u=currentUser();$rooms=readStore($roomsFile);$code=strtoupper(trim((string)($in['code']??'')));$r=getRoom($rooms,$code);if($r['host_id']!==$u['id'])respond(false,null,'فقط میزبان می‌تواند بازی را شروع کند.',403);if(count($r['members'])+(int)($r['bot_count']??0)>$r['capacity'])respond(false,null,'تعداد بازیکنان و بات‌ها از ظرفیت بیشتر است.',409);$r['status']='playing';$rooms[$code]=$r;writeStore($roomsFile,$rooms);respond(true,['room'=>roomPublic($r,$u['id'])]);
case 'room_state':
  $u=currentUser();$rooms=readStore($roomsFile);$code=strtoupper(trim((string)($in['code']??'')));$r=getRoom($rooms,$code);if(!isset($r['members'][$u['id']]))respond(false,null,'عضو این اتاق نیستید.',403);$r['members'][$u['id']]['last_seen']=time();$pos=$in['position']??[];if(is_array($pos)){foreach(['x','y','z','yaw','pitch','hp','kills'] as $k){if(isset($pos[$k])&&is_numeric($pos[$k]))$r['members'][$u['id']]['position'][$k]=max(-1000,min(1000,(float)$pos[$k]));}if(isset($pos['alive']))$r['members'][$u['id']]['position']['alive']=(bool)$pos['alive'];}$rooms[$code]=$r;writeStore($roomsFile,$rooms);$players=[];foreach($r['members'] as $id=>$m)if(time()-($m['last_seen']??0)<20)$players[]=['id'=>$id,'username'=>$m['username'],'display_name'=>$m['display_name'],'position'=>$m['position']??[],'color'=>sprintf('0x%06x',hexdec(substr(hash('sha256',$id),0,6)))];respond(true,['room'=>roomPublic($r,$u['id']),'players'=>$players]);
case 'leave_room':
  $u=currentUser();$rooms=readStore($roomsFile);$code=strtoupper(trim((string)($in['code']??'')));$r=getRoom($rooms,$code);unset($r['members'][$u['id']]);if(!$r['members'])unset($rooms[$code]);else{if($r['host_id']===$u['id'])$r['host_id']=array_key_first($r['members']);$rooms[$code]=$r;}writeStore($roomsFile,$rooms);respond(true,['message'=>'از اتاق خارج شدید.']);
default: respond(false,null,'عملیات ناشناخته است.',404);
}
