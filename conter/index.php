<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<title>Counter Arena 3D</title>
<style>
  html, body { margin: 0; height: 100%; overflow: hidden; background: #000; font-family: Arial, sans-serif; user-select: none; -webkit-user-select: none; touch-action: none; -webkit-touch-callout: none; }
  canvas { display: block; }
  body.playing canvas { cursor: none; }
  #hud { position: fixed; inset: 0; pointer-events: none; color: #fff; text-shadow: 0 0 4px #000, 0 0 2px #000; }
  #crosshair { position: absolute; left: 50%; top: 50%; width: 24px; height: 24px; transform: translate(-50%, -50%); }
  #crosshair::before, #crosshair::after { content: ""; position: absolute; background: #3f3; box-shadow: 0 0 2px #000; }
  #crosshair::before { left: 11px; top: 0; width: 2px; height: 24px; }
  #crosshair::after { top: 11px; left: 0; height: 2px; width: 24px; }
  #crosshair i { position: absolute; left: 9px; top: 9px; width: 6px; height: 6px; background: #000; z-index: 2; }
  #hitmarker { position: absolute; left: 50%; top: 50%; width: 30px; height: 30px; transform: translate(-50%, -50%) rotate(45deg); opacity: 0; }
  #hitmarker::before, #hitmarker::after { content: ""; position: absolute; background: #fff; }
  #hitmarker::before { left: 14px; top: 0; width: 2px; height: 30px; }
  #hitmarker::after { top: 14px; left: 0; height: 2px; width: 30px; }
  #hitmarker.kill::before, #hitmarker.kill::after { background: #f22; }
  .stat { position: absolute; font-size: 22px; font-weight: bold; }
  #hpbox { left: 20px; bottom: 20px; width: 240px; }
  #hpbar { height: 12px; background: rgba(0,0,0,.5); border: 1px solid #000; margin-top: 4px; }
  #hpfill { height: 100%; width: 100%; background: #4c4; }
  #allybox { left: 20px; bottom: 90px; font-size: 15px; }
  #ammobox { right: 20px; bottom: 20px; text-align: right; }
  #wname { font-size: 16px; color: #ffcf66; }
  #ammo { font-size: 38px; }
  #nades { font-size: 15px; color: #cfe; }
  #score { left: 20px; top: 20px; }
  #wave { right: 20px; top: 20px; }
  #mapname { position: absolute; left: 20px; top: 52px; font-size: 13px; opacity: .8; }
  #weapons { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; pointer-events: auto; }
  .wslot { padding: 6px 10px; background: rgba(0,0,0,.45); border: 2px solid transparent; border-radius: 6px; font-size: 13px; cursor: pointer; }
  .wslot.active { border-color: #ff9d00; color: #ffcf66; }
  #banner { position: absolute; left: 50%; top: 18%; transform: translateX(-50%); font-size: 36px; font-weight: bold; color: #ffcf66; opacity: 0; transition: opacity .3s; text-align: center; white-space: nowrap; }
  #hurt { position: absolute; inset: 0; background: radial-gradient(transparent 35%, rgba(255,0,0,.65)); opacity: 0; }
  #flashbang { position: absolute; inset: 0; background: #fff; opacity: 0; }
  #scope { position: absolute; inset: 0; opacity: 0; background:
      linear-gradient(#000, #000) center / 2px 70vh no-repeat,
      linear-gradient(#000, #000) center / 70vh 2px no-repeat,
      radial-gradient(circle at center, transparent 0, transparent 35vh, #000 35.3vh); }

  #menu { position: fixed; inset: 0; background: rgba(0,0,0,.8); color: #fff; display: flex; flex-direction: column;
          align-items: center; justify-content: center; text-align: center; cursor: pointer; z-index: 20; }
  #menu h1 { font-size: 46px; margin: 0 0 8px; color: #ff9d00; }
  #menu p { margin: 3px 10px; font-size: 16px; }
  #maps { display: flex; gap: 10px; margin: 14px 0 8px; flex-wrap: wrap; justify-content: center; }
  .mapbtn { padding: 10px 16px; border: 2px solid #666; border-radius: 8px; background: rgba(255,255,255,.08); font-size: 16px; cursor: pointer; }
  .mapbtn.sel { border-color: #ff9d00; background: rgba(255,157,0,.25); color: #ffcf66; }
  #playbtn { margin-top: 10px; padding: 12px 40px; font-size: 22px; font-weight: bold; background: #ff9d00; color: #000; border-radius: 8px; }

  /* touch UI */
  #touch { position: fixed; inset: 0; z-index: 10; display: none; pointer-events: none; }
  body.touch #touch { display: block; }
  #joyzone { position: absolute; left: 0; top: 0; width: 38%; height: 100%; pointer-events: auto; }
  #lookzone { position: absolute; right: 0; top: 0; width: 62%; height: 100%; pointer-events: auto; }
  #joybase, #joyknob { position: absolute; border-radius: 50%; display: none; pointer-events: none; transform: translate(-50%, -50%); }
  #joybase { width: 120px; height: 120px; border: 2px solid rgba(255,255,255,.4); background: rgba(255,255,255,.08); }
  #joyknob { width: 54px; height: 54px; background: rgba(255,255,255,.35); }
  .tbtn { position: absolute; width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,.16); border: 2px solid rgba(255,255,255,.5);
          color: #fff; font: bold 12px Arial; display: flex; align-items: center; justify-content: center; pointer-events: auto; text-align: center; line-height: 1.1; }
  .tbtn.on { background: rgba(255,157,0,.5); }
  #tFire { right: 26px; bottom: 30px; width: 92px; height: 92px; background: rgba(255,60,40,.3); font-size: 16px; }
  #tAim { right: 134px; bottom: 26px; }
  #tReload { right: 112px; bottom: 96px; }
  #tJump { right: 26px; bottom: 138px; }
  #tFrag { right: 200px; bottom: 90px; width: 52px; height: 52px; }
  #tSmoke { right: 200px; bottom: 150px; width: 52px; height: 52px; }
  #tKnife { right: 112px; bottom: 160px; width: 52px; height: 52px; }
  #tNext { right: 26px; bottom: 214px; width: 52px; height: 52px; }
  #tPause { right: 130px; top: 10px; width: 44px; height: 44px; font-size: 16px; }
  body.touch .stat { font-size: 15px; }
  body.touch #ammo { font-size: 24px; }
  body.touch #hpbox { left: 14px; bottom: 12px; width: 150px; }
  body.touch #allybox { bottom: 58px; left: 14px; font-size: 12px; }
  body.touch #ammobox { right: auto; left: 50%; transform: translateX(-50%); bottom: 8px; text-align: center; }
  body.touch #weapons { bottom: auto; top: 8px; }
  body.touch .wslot { padding: 3px 7px; font-size: 11px; }
  body.touch #score { left: 14px; top: 8px; } body.touch #mapname { left: 14px; top: 30px; }
  body.touch #wave { right: 14px; top: 8px; }
  body.touch #banner { font-size: 24px; }
  #rotate { position: fixed; inset: 0; background: #000; color: #fff; z-index: 50; display: none; align-items: center; justify-content: center; flex-direction: column; font-size: 22px; text-align: center; }
  #rotate .ic { font-size: 70px; }
</style>
</head>
<body>
<div id="hud">
  <div id="crosshair"><i></i></div>
  <div id="hitmarker"></div>
  <div id="scope"></div>
  <div class="stat" id="score">Kills: 0</div>
  <div id="mapname"></div>
  <div class="stat" id="wave">Wave: 1</div>
  <div class="stat" id="hpbox"><span id="health">HP: 100</span><div id="hpbar"><div id="hpfill"></div></div></div>
  <div class="stat" id="allybox">Ally: --</div>
  <div class="stat" id="ammobox"><div id="wname">Pistol</div><div id="ammo">12 / 60</div><div id="nades"></div></div>
  <div id="weapons"></div>
  <div id="banner"></div>
  <div id="hurt"></div>
</div>

<div id="touch">
  <div id="joyzone"></div>
  <div id="lookzone"></div>
  <div id="joybase"></div><div id="joyknob"></div>
  <div class="tbtn" id="tFire">FIRE</div>
  <div class="tbtn" id="tAim">AIM</div>
  <div class="tbtn" id="tReload">RELOAD</div>
  <div class="tbtn" id="tJump">JUMP</div>
  <div class="tbtn" id="tFrag">FRAG</div>
  <div class="tbtn" id="tSmoke">SMOKE</div>
  <div class="tbtn" id="tKnife">KNIFE</div>
  <div class="tbtn" id="tNext">NEXT</div>
  <div class="tbtn" id="tPause">II</div>
</div>

<div id="menu">
  <h1>COUNTER ARENA 3D</h1>
  <p>Choose a map</p>
  <div id="maps"></div>
  <p id="menuLine">Click to play</p>
  <p class="desk">WASD move | Mouse look | Left click shoot | Right click aim/scope | R reload | Space jump | Shift sprint</p>
  <p class="desk">1-5 or wheel: weapons (5 = knife) | V quick knife | G frag grenade | H smoke grenade | Esc pause</p>
  <p class="mob" style="display:none">Left thumb: move | Right side: look | Buttons: fire, aim, reload, jump, grenades, knife</p>
  <div id="playbtn">PLAY</div>
  <p id="msg" style="color:#f55;font-size:24px;margin-top:14px"></p>
</div>

<div id="mp-panel" dir="rtl">
 <div class="mp-card">
  <div class="mp-head"><div><small>COUNTER ARENA / NETWORK</small><h2>اتاق فرمان آنلاین</h2></div><button id="mp-close" aria-label="بستن">×</button></div>
  <div id="mp-status" class="mp-status">برای بازی آفلاین نیازی به حساب نیست؛ برای آنلاین وارد حساب شوید.</div>
  <section id="mp-auth">
   <div class="mp-tabs"><button class="active" data-auth="login">ورود</button><button data-auth="register">ساخت حساب</button></div>
   <label>نام کاربری<input id="mp-user" autocomplete="username" maxlength="24" placeholder="مثلاً PlayerOne"></label>
   <label>رمز عبور<input id="mp-pass" type="password" autocomplete="current-password" minlength="8" placeholder="حداقل ۸ کاراکتر"></label>
   <label id="mp-display-wrap" hidden>نام نمایشی<input id="mp-display" maxlength="28" placeholder="نامی که دوستان می‌بینند"></label>
   <button class="mp-primary" id="mp-auth-submit">ورود به حساب</button>
   <button id="mp-offline-auth" class="mp-ghost mp-wide" type="button">شروع بازی آفلاین بدون حساب</button>
  </section>
  <section id="mp-account" hidden><div class="mp-account-line"><b id="mp-welcome"></b><button id="mp-logout" class="mp-ghost">خروج</button></div>
   <div class="mp-grid"><button id="mp-offline" class="mp-ghost">شروع بازی آفلاین</button><button id="mp-continue" class="mp-ghost">ادامه به منوی بازی</button></div>
   <div class="mp-room-list"><b>تنظیمات سرور جدید</b>
    <label>نقشه<select id="mp-new-map"><option value="0">Forest Outpost</option><option value="1">Desert Town</option><option value="2">Industrial Yard</option><option value="3">Snow Base</option></select></label>
    <label>حالت بازی<select id="mp-new-mode"><option value="coop">همکاری (Co-op)</option><option value="team">تیمی</option><option value="ffa">همه علیه همه</option></select></label>
    <label>ظرفیت بازیکنان<select id="mp-new-cap"><option>2</option><option>4</option><option>6</option><option selected>8</option><option>12</option></select></label>
    <label>بات‌های اتاق<select id="mp-new-bots"><option value="0">بدون بات</option><option value="2">۲ بات</option><option value="4">۴ بات</option><option value="6">۶ بات</option></select></label>
    <label class="mp-check"><input type="checkbox" id="mp-new-private"> اتاق خصوصی (فقط با کد دعوت)</label>
    <div class="mp-grid"><button id="mp-create" class="mp-primary">＋ ساخت سرور</button><button id="mp-refresh" class="mp-ghost">به‌روزرسانی</button></div>
   </div>
   <label>کد دعوت / اتاق خصوصی<input id="mp-code" maxlength="8" placeholder="مثلاً A7K2PQ"></label><button id="mp-join" class="mp-primary">پیوستن به اتاق</button>
   <div class="mp-room-list"><b>اتاق‌های عمومی آنلاین</b><div id="mp-rooms">هنوز اتاقی بارگذاری نشده است.</div></div>
   <div class="mp-room-list"><b>فهرست کاربران ثبت‌نام‌شده</b><div id="mp-users">در حال بارگذاری…</div></div>
  </section>
  <section id="mp-room" hidden><div class="mp-room-title"><span>اتاق شما</span><strong id="mp-room-code">------</strong><button id="mp-copy" class="mp-ghost">کپی دعوت</button></div>
   <label>نقشه گروهی<select id="mp-map"><option value="0">Forest Outpost</option><option value="1">Desert Town</option><option value="2">Industrial Yard</option><option value="3">Snow Base</option></select></label>
   <label>حالت بازی<select id="mp-mode"><option value="coop">همکاری (Co-op)</option><option value="team">تیمی</option><option value="ffa">همه علیه همه</option></select></label>
   <label>ظرفیت سرور<select id="mp-cap"><option>2</option><option>4</option><option>6</option><option>8</option><option>12</option></select></label>
   <label>بات‌های اتاق<select id="mp-bots"><option value="0">بدون بات</option><option value="2">۲ بات</option><option value="4">۴ بات</option><option value="6">۶ بات</option></select></label>
   <label class="mp-check"><input type="checkbox" id="mp-private"> اتاق خصوصی (از فهرست عمومی مخفی باشد)</label>
   <div class="mp-room-list"><b>بازیکنان و بات‌ها</b><div id="mp-members"></div></div>
   <div class="mp-grid"><button id="mp-save" class="mp-primary">ذخیره تنظیمات</button><button id="mp-leave" class="mp-ghost">ترک اتاق</button></div>
   <button id="mp-start" class="mp-start">شروع بازی گروهی</button>
  </section>
  <div class="mp-footer">بازی آفلاین و بخش سه‌بعدی اصلی حفظ شده است. برای آنلاین، PHP روی هاست اجرا شود.</div>
 </div>
</div>
<button id="mp-open">☷ آنلاین / حساب کاربری</button>
<style>
#mp-open{position:fixed;z-index:19;top:14px;right:14px;border:1px solid #ffad3d;background:#111d;color:#ffbd66;padding:11px 15px;border-radius:10px;font-weight:800;cursor:pointer;backdrop-filter:blur(12px)}
#mp-panel{display:none;position:fixed;inset:0;z-index:60;background:rgba(3,7,13,.82);backdrop-filter:blur(10px);overflow:auto;align-items:center;justify-content:center;padding:18px;box-sizing:border-box;color:#f4f6fa;font-family:Tahoma,Arial,sans-serif;touch-action:pan-y}
#mp-panel.show{display:flex}.mp-card{width:min(540px,100%);max-height:94vh;overflow:auto;background:linear-gradient(145deg,#171e2b,#0d111a);border:1px solid #384458;border-radius:20px;padding:22px;box-sizing:border-box;box-shadow:0 24px 90px #0009}
.mp-head,.mp-account-line,.mp-room-title{display:flex;align-items:center;justify-content:space-between;gap:10px}.mp-head small{color:#ffb14b;letter-spacing:2px;font-size:10px}.mp-head h2{margin:5px 0 15px;font-size:25px}.mp-head button{background:#273142;color:#fff;border:0;border-radius:9px;font-size:24px;width:38px;height:38px;cursor:pointer}.mp-status{background:#202a39;border:1px solid #344156;border-radius:10px;padding:11px;margin-bottom:14px;font-size:13px;line-height:1.8;color:#d8e1ed}.mp-status.error{border-color:#9d4141;color:#ffbaba}.mp-status.ok{border-color:#397b5d;color:#b4f5d0}
#mp-panel label{display:block;font-size:12px;color:#c4cede;margin:12px 0}#mp-panel input,#mp-panel select{display:block;width:100%;box-sizing:border-box;margin-top:7px;padding:12px;border:1px solid #39465a;border-radius:9px;background:#0c121c;color:#fff;outline:none;font:inherit}#mp-panel input:focus,#mp-panel select:focus{border-color:#ffad3d}
.mp-tabs,.mp-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}.mp-tabs{margin-bottom:14px}.mp-tabs button,.mp-primary,.mp-ghost,.mp-start{border-radius:9px;padding:12px;border:1px solid #39465a;background:#202a39;color:#eaf0f8;cursor:pointer;font-weight:700}.mp-tabs button.active,.mp-primary{background:#ffad3d;border-color:#ffad3d;color:#17110a}.mp-ghost{background:#202a39;color:#eaf0f8}.mp-primary,.mp-start{width:100%;margin-top:6px}.mp-start{background:#287d59;border-color:#35996e;color:white}.mp-room-list{margin:15px 0;background:#0c121c;border:1px solid #303b4d;border-radius:10px;padding:13px}.mp-room-list> b{display:block;margin-bottom:8px}.mp-member,.mp-room-item{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:9px 0;border-top:1px solid #283244;font-size:12px}.mp-wide{width:100%;margin:8px 0}.mp-check{display:flex!important;align-items:center;gap:9px}.mp-check input{width:auto!important;margin:0!important;accent-color:#ffad3d}.mp-room-item button{flex-shrink:0;padding:7px 10px;background:#ffad3d;color:#17110a;border:0;border-radius:7px;cursor:pointer}.mp-room-title{background:#0c121c;padding:12px;border-radius:10px;margin:10px 0}.mp-room-title strong{font-size:22px;color:#ffbd66;letter-spacing:3px}.mp-room-title button{padding:7px}.mp-footer{font-size:10px;line-height:1.8;color:#8e9db2;margin-top:16px;text-align:center}#mp-panel [hidden]{display:none!important}
@media(max-width:600px){#mp-open{top:auto;bottom:12px;right:12px;font-size:11px;padding:9px}.mp-card{padding:16px}.mp-head h2{font-size:21px}}
</style>

<div id="rotate"><div class="ic">&#x21BB;</div><div>Rotate your device to landscape</div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(() => {
  "use strict";
  const $ = id => document.getElementById(id);
  const V3 = THREE.Vector3;
  const ARENA = 50;
  const rnd = (a, b) => a + Math.random() * (b - a);
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const wrapA = a => { while (a > Math.PI) a -= 2 * Math.PI; while (a < -Math.PI) a += 2 * Math.PI; return a; };

  // ====================== RENDERER / SCENE ======================
  const scene = new THREE.Scene();
  scene.fog = new THREE.Fog(0xc4d8e8, 25, 110);
  const camera = new THREE.PerspectiveCamera(75, innerWidth / innerHeight, 0.05, 400);
  camera.rotation.order = "YXZ";
  const renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setSize(innerWidth, innerHeight);
  renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
  renderer.shadowMap.enabled = true;
  document.body.appendChild(renderer.domElement);
  scene.add(camera);

  const hemi = new THREE.HemisphereLight(0xdfeeff, 0x4a5a3a, 0.8);
  scene.add(hemi);
  const sun = new THREE.DirectionalLight(0xfff2d8, 0.85);
  sun.position.set(30, 50, 20);
  sun.castShadow = true;
  sun.shadow.camera.left = sun.shadow.camera.bottom = -60;
  sun.shadow.camera.right = sun.shadow.camera.top = 60;
  sun.shadow.camera.far = 150;
  sun.shadow.mapSize.set(2048, 2048);
  scene.add(sun);

  function makeTex(size, draw, rx = 1, ry = 1) {
    const c = document.createElement("canvas"); c.width = c.height = size;
    const g = c.getContext("2d"); draw(g, size);
    const t = new THREE.CanvasTexture(c);
    t.wrapS = t.wrapT = THREE.RepeatWrapping; t.repeat.set(rx, ry); t.anisotropy = 4;
    return t;
  }

  // ====================== THEMES ======================
  const THEMES = [
    { name: "Forest Outpost", ground: "grass", fog: 0xc4d8e8, fogN: 25, fogF: 110, sky: ["#2f6fb8", "#8fb9e0", "#c4d8e8", "#6d8a55"], mtn: 0x5d6f63, hemiS: 0xdfeeff, hemiG: 0x4a5a3a, sun: 0xfff2d8, wall: "concrete", house: "brick", outer: "pine" },
    { name: "Desert Town", ground: "sand", fog: 0xe6d3a8, fogN: 30, fogF: 130, sky: ["#3a86c9", "#9cc6e6", "#ecdcb6", "#b99a62"], mtn: 0xa07a50, hemiS: 0xfff2d0, hemiG: 0x9a8458, sun: 0xfff0c0, wall: "adobe", house: "adobe", outer: "none" },
    { name: "Industrial Yard", ground: "asphalt", fog: 0xa3abb3, fogN: 25, fogF: 105, sky: ["#59718a", "#9aa9b8", "#b9c1c8", "#555c63"], mtn: 0x4a5058, hemiS: 0xd8e0e8, hemiG: 0x50555a, sun: 0xf0f4ff, wall: "concrete", house: "metal", outer: "none" },
    { name: "Snow Base", ground: "snow", fog: 0xdfe8ee, fogN: 18, fogF: 85, sky: ["#6f93b8", "#b8cde0", "#e6eef4", "#d8e2ea"], mtn: 0xcfd9e2, hemiS: 0xeaf2ff, hemiG: 0x9aa8b4, sun: 0xffffff, wall: "concrete", house: "wood", outer: "snowpine" }
  ];
  const groundCache = {}, skyCache = {};
  function groundTex(kind) {
    if (groundCache[kind]) return groundCache[kind];
    const base = { grass: "#4f7a34", sand: "#cdae72", asphalt: "#4b4d50", snow: "#eaf0f4" }[kind];
    groundCache[kind] = makeTex(256, (g, s) => {
      g.fillStyle = base; g.fillRect(0, 0, s, s);
      if (kind === "grass") {
        for (let i = 0; i < 6000; i++) { g.fillStyle = `hsl(${85 + Math.random() * 35},${35 + Math.random() * 25}%,${22 + Math.random() * 22}%)`; g.fillRect(Math.random() * s, Math.random() * s, 2, 2 + Math.random() * 4); }
        for (let i = 0; i < 25; i++) { g.fillStyle = "rgba(110,85,50,.25)"; g.beginPath(); g.arc(Math.random() * s, Math.random() * s, 6 + Math.random() * 14, 0, 7); g.fill(); }
      } else if (kind === "sand") {
        for (let i = 0; i < 5000; i++) { g.fillStyle = `rgba(${Math.random() < .5 ? "120,90,40" : "255,240,200"},${Math.random() * .15})`; g.fillRect(Math.random() * s, Math.random() * s, 2, 2); }
        for (let i = 0; i < 10; i++) { g.strokeStyle = "rgba(150,120,70,.2)"; g.lineWidth = 2; g.beginPath(); const y = Math.random() * s; g.moveTo(0, y); g.bezierCurveTo(s * .3, y - 10, s * .6, y + 10, s, y); g.stroke(); }
      } else if (kind === "asphalt") {
        for (let i = 0; i < 6000; i++) { g.fillStyle = `rgba(${Math.random() < .5 ? 0 : 255},${Math.random() < .5 ? 0 : 255},${Math.random() < .5 ? 0 : 255},${Math.random() * .1})`; g.fillRect(Math.random() * s, Math.random() * s, 2, 2); }
        g.strokeStyle = "rgba(230,200,60,.5)"; g.lineWidth = 4; g.setLineDash([24, 18]); g.beginPath(); g.moveTo(0, s * .5); g.lineTo(s, s * .5); g.stroke();
      } else {
        for (let i = 0; i < 4000; i++) { g.fillStyle = `rgba(${Math.random() < .5 ? "150,180,210" : "255,255,255"},${Math.random() * .2})`; g.fillRect(Math.random() * s, Math.random() * s, 3, 3); }
      }
    }, 80, 80);
    return groundCache[kind];
  }
  function skyTex(th) {
    if (skyCache[th.name]) return skyCache[th.name];
    skyCache[th.name] = makeTex(256, (g, s) => {
      const gr = g.createLinearGradient(0, 0, 0, s);
      gr.addColorStop(0, th.sky[0]); gr.addColorStop(.45, th.sky[1]); gr.addColorStop(.55, th.sky[2]); gr.addColorStop(1, th.sky[3]);
      g.fillStyle = gr; g.fillRect(0, 0, s, s);
    });
    return skyCache[th.name];
  }
  const skyMesh = new THREE.Mesh(new THREE.SphereGeometry(300, 24, 16), new THREE.MeshBasicMaterial({ map: skyTex(THEMES[0]), side: THREE.BackSide, fog: false }));
  scene.add(skyMesh);
  const floor = new THREE.Mesh(new THREE.PlaneGeometry(320, 320), new THREE.MeshStandardMaterial({ map: groundTex("grass"), roughness: 1 }));
  floor.rotation.x = -Math.PI / 2; floor.receiveShadow = true; scene.add(floor);

  // ====================== MATERIALS ======================
  const crateTex = makeTex(128, (g, s) => {
    g.fillStyle = "#9c6b30"; g.fillRect(0, 0, s, s);
    for (let i = 0; i < 400; i++) { g.fillStyle = `rgba(60,35,10,${Math.random() * .25})`; g.fillRect(Math.random() * s, Math.random() * s, 20, 1); }
    g.strokeStyle = "#5a3a15"; g.lineWidth = 8; g.strokeRect(4, 4, s - 8, s - 8);
    g.lineWidth = 5; g.beginPath(); g.moveTo(8, 8); g.lineTo(s - 8, s - 8); g.moveTo(s - 8, 8); g.lineTo(8, s - 8); g.stroke();
  });
  const concreteTex = makeTex(128, (g, s) => {
    g.fillStyle = "#8c8c88"; g.fillRect(0, 0, s, s);
    for (let i = 0; i < 1500; i++) { g.fillStyle = `rgba(0,0,0,${Math.random() * .12})`; g.fillRect(Math.random() * s, Math.random() * s, 2, 2); }
    g.strokeStyle = "rgba(0,0,0,.25)"; g.lineWidth = 2; g.strokeRect(0, 0, s, s);
  });
  const brickTex = makeTex(128, (g, s) => {
    g.fillStyle = "#8a4b3a"; g.fillRect(0, 0, s, s); g.strokeStyle = "#cbbfae"; g.lineWidth = 3;
    for (let y = 0; y < s; y += 16) { g.beginPath(); g.moveTo(0, y); g.lineTo(s, y); g.stroke(); for (let x = (y / 16) % 2 ? 0 : 16; x < s; x += 32) { g.beginPath(); g.moveTo(x, y); g.lineTo(x, y + 16); g.stroke(); } }
  }, 2, 1);
  const adobeTex = makeTex(128, (g, s) => {
    g.fillStyle = "#c8a66e"; g.fillRect(0, 0, s, s);
    for (let i = 0; i < 2500; i++) { g.fillStyle = `rgba(${Math.random() < .5 ? "90,60,20" : "255,240,200"},${Math.random() * .12})`; g.fillRect(Math.random() * s, Math.random() * s, 3, 3); }
  }, 2, 1);
  const woodTex = makeTex(128, (g, s) => {
    g.fillStyle = "#7a5230"; g.fillRect(0, 0, s, s);
    for (let y = 0; y < s; y += 16) { g.fillStyle = `rgba(0,0,0,${.1 + Math.random() * .15})`; g.fillRect(0, y, s, 3); for (let i = 0; i < 20; i++) { g.fillStyle = "rgba(40,20,5,.2)"; g.fillRect(Math.random() * s, y + Math.random() * 14, 18, 1); } }
  }, 2, 1);
  const metalTex = makeTex(128, (g, s) => {
    g.fillStyle = "#6f7c86"; g.fillRect(0, 0, s, s);
    for (let x = 0; x < s; x += 8) { g.fillStyle = "rgba(0,0,0,.2)"; g.fillRect(x, 0, 3, s); g.fillStyle = "rgba(255,255,255,.1)"; g.fillRect(x + 3, 0, 2, s); }
    for (let i = 0; i < 600; i++) { g.fillStyle = "rgba(120,60,20,.08)"; g.fillRect(Math.random() * s, Math.random() * s, 4, 4); }
  }, 3, 1);
  const floorTex = makeTex(128, (g, s) => {
    g.fillStyle = "#6a5039"; g.fillRect(0, 0, s, s);
    for (let x = 0; x < s; x += 16) { g.fillStyle = "rgba(0,0,0,.25)"; g.fillRect(x, 0, 2, s); }
    for (let i = 0; i < 300; i++) { g.fillStyle = "rgba(0,0,0,.08)"; g.fillRect(Math.random() * s, Math.random() * s, 10, 1); }
  }, 6, 6);
  function containerTex(col) {
    return makeTex(128, (g, s) => {
      g.fillStyle = col; g.fillRect(0, 0, s, s);
      g.fillStyle = "rgba(0,0,0,.25)"; for (let x = 0; x < s; x += 16) g.fillRect(x, 0, 6, s);
      g.fillStyle = "rgba(255,255,255,.05)"; for (let i = 0; i < 300; i++) g.fillRect(Math.random() * s, Math.random() * s, 3, 3);
    }, 4, 1);
  }
  const M = {
    crate: new THREE.MeshStandardMaterial({ map: crateTex, roughness: .9 }),
    concrete: new THREE.MeshStandardMaterial({ map: concreteTex, roughness: .95 }),
    brick: new THREE.MeshStandardMaterial({ map: brickTex, roughness: .95 }),
    adobe: new THREE.MeshStandardMaterial({ map: adobeTex, roughness: 1 }),
    wood: new THREE.MeshStandardMaterial({ map: woodTex, roughness: .9 }),
    metal: new THREE.MeshStandardMaterial({ map: metalTex, roughness: .6, metalness: .35 }),
    floor: new THREE.MeshStandardMaterial({ map: floorTex, roughness: .9 }),
    roof: new THREE.MeshStandardMaterial({ color: 0x4a4a48, roughness: .9 }),
    cont: [0x9a2a22, 0x2a5a8a, 0x3a6a3a].map(c => new THREE.MeshStandardMaterial({ map: containerTex("#" + c.toString(16).padStart(6, "0")), roughness: .7, metalness: .3 })),
    sand: new THREE.MeshStandardMaterial({ color: 0xb8a572, roughness: 1 }),
    bark: new THREE.MeshStandardMaterial({ color: 0x4a3322, roughness: 1 }),
    pine: new THREE.MeshStandardMaterial({ color: 0x1f4d2a, roughness: .9, flatShading: true }),
    snowpine: new THREE.MeshStandardMaterial({ color: 0xa9c4b8, roughness: .9, flatShading: true }),
    leaf: new THREE.MeshStandardMaterial({ color: 0x3d7a2c, roughness: .9, flatShading: true }),
    palm: new THREE.MeshStandardMaterial({ color: 0x4f8a2a, roughness: .9, side: THREE.DoubleSide }),
    bush: new THREE.MeshStandardMaterial({ color: 0x2f6a2a, roughness: 1, flatShading: true }),
    rock: new THREE.MeshStandardMaterial({ color: 0x77776f, roughness: 1, flatShading: true }),
    snowrock: new THREE.MeshStandardMaterial({ color: 0xcfd8de, roughness: 1, flatShading: true }),
    barrelR: new THREE.MeshStandardMaterial({ color: 0xa02a22, roughness: .5, metalness: .5 }),
    barrelB: new THREE.MeshStandardMaterial({ color: 0x2a4a8a, roughness: .5, metalness: .5 }),
    dark: new THREE.MeshStandardMaterial({ color: 0x555550, roughness: .9 }),
    pole: new THREE.MeshStandardMaterial({ color: 0x3a3a3a, roughness: .6, metalness: .5 })
  };

  // ====================== WORLD (rebuilt per map) ======================
  const boxes = [];     // {minX,maxX,minZ,maxZ,top}
  const blockers = [];  // meshes blocking bullets / sight
  const mapObjs = [];
  const placed = [];
  let theme = THEMES[0], houseMat = M.brick, wallMat = M.concrete;
  let snowPts = null;
  const add = o => { scene.add(o); mapObjs.push(o); return o; };

  function addBox(x, z, w, h, d, mat, y0 = 0, noBlock = false, noCol = false) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat);
    m.position.set(x, y0 + h / 2, z);
    m.castShadow = m.receiveShadow = true;
    add(m);
    if (!noCol) boxes.push({ minX: x - w / 2, maxX: x + w / 2, minZ: z - d / 2, maxZ: z + d / 2, top: y0 + h });
    if (!noBlock) blockers.push(m);
    return m;
  }
  function addCollider(x, z, hw, hd, top) { boxes.push({ minX: x - hw, maxX: x + hw, minZ: z - hd, maxZ: z + hd, top }); }

  function collide(pos, feet, r) {
    for (const b of boxes) {
      if (b.top <= feet + 0.6) continue;
      const cx = clamp(pos.x, b.minX, b.maxX), cz = clamp(pos.z, b.minZ, b.maxZ);
      const dx = pos.x - cx, dz = pos.z - cz, d = Math.hypot(dx, dz);
      if (d < r) {
        if (d < 1e-5) {
          const l = pos.x - b.minX, rr = b.maxX - pos.x, f = pos.z - b.minZ, bk = b.maxZ - pos.z;
          const m = Math.min(l, rr, f, bk);
          if (m === l) pos.x = b.minX - r; else if (m === rr) pos.x = b.maxX + r; else if (m === f) pos.z = b.minZ - r; else pos.z = b.maxZ + r;
        } else { pos.x += (dx / d) * (r - d); pos.z += (dz / d) * (r - d); }
      }
    }
    pos.x = clamp(pos.x, -ARENA + 1, ARENA - 1);
    pos.z = clamp(pos.z, -ARENA + 1, ARENA - 1);
  }
  function groundAt(x, z, feet) {
    let g = 0;
    for (const b of boxes) {
      if (b.top <= feet + 0.6 && b.top > g && x > b.minX - 0.3 && x < b.maxX + 0.3 && z > b.minZ - 0.3 && z < b.maxZ + 0.3) g = b.top;
    }
    return g;
  }

  const free = (x, z, r) => placed.every(p => Math.hypot(p.x - x, p.z - z) > p.r + r);
  const reserve = (x, z, r) => placed.push({ x, z, r });
  function scatter(n, r, fn, range = 46) {
    let c = 0, tries = 0;
    while (c < n && tries < n * 25) {
      tries++;
      const x = rnd(-range, range), z = rnd(-range, range);
      if (!free(x, z, r)) continue;
      reserve(x, z, r); fn(x, z); c++;
    }
  }

  // ---- prop builders
  function crateAt(x, z) {
    const s = rnd(1.1, 1.5);
    addBox(x, z, s, 1.1, s, M.crate);
    if (Math.random() < .5) addBox(x, z, s * .9, 1.1, s * .9, M.crate, 1.1);
  }
  function barrelAt(x, z) {
    const m = new THREE.Mesh(new THREE.CylinderGeometry(.4, .4, 1.1, 14), Math.random() < .5 ? M.barrelR : M.barrelB);
    m.position.set(x, .55, z); m.castShadow = true; add(m); blockers.push(m);
    addCollider(x, z, .4, .4, 1.1);
  }
  function sandAt(x, z) {
    const rot = Math.random() < .5;
    for (let k = 0; k < 3; k++) {
      const ox = rot ? 0 : (k - 1) * 1.6, oz = rot ? (k - 1) * 1.6 : 0;
      addBox(x + ox, z + oz, rot ? .8 : 1.6, .5, rot ? 1.6 : .8, M.sand);
      addBox(x + ox, z + oz, rot ? .7 : 1.4, .45, rot ? 1.4 : .7, M.sand, .5);
    }
  }
  function rockAt(x, z, mat) {
    const s = rnd(.9, 2);
    const m = new THREE.Mesh(new THREE.DodecahedronGeometry(s, 0), mat);
    m.scale.y = .75; m.position.set(x, s * .6, z); m.rotation.y = Math.random() * 6; m.castShadow = m.receiveShadow = true;
    add(m); blockers.push(m);
    addCollider(x, z, s * .75, s * .75, s * 1.0);
  }
  function treeAt(x, z, s, kind) {
    const trunk = new THREE.Mesh(new THREE.CylinderGeometry(.18 * s, .28 * s, 3.2 * s, 8), M.bark);
    trunk.position.set(x, 1.6 * s, z); trunk.castShadow = true; add(trunk); blockers.push(trunk);
    if (kind === "pine" || kind === "snowpine" || (kind === "mixed" && Math.random() < .6)) {
      const mat = kind === "snowpine" ? M.snowpine : M.pine;
      [[2.3, 2.6, 3.2], [1.8, 2.3, 4.6], [1.2, 2.0, 5.9]].forEach(([r, h, y]) => {
        const c = new THREE.Mesh(new THREE.ConeGeometry(r * s, h * s, 9), mat);
        c.position.set(x, y * s, z); c.castShadow = true; add(c); blockers.push(c);
      });
    } else {
      for (let k = 0; k < 4; k++) {
        const c = new THREE.Mesh(new THREE.IcosahedronGeometry(rnd(1.2, 1.7) * s, 1), M.leaf);
        c.position.set(x + rnd(-1, 1) * s, (4 + rnd(-.3, 1.3)) * s, z + rnd(-1, 1) * s); c.castShadow = true; add(c); blockers.push(c);
      }
    }
    addCollider(x, z, .35 * s, .35 * s, 99);
  }
  function palmAt(x, z) {
    const s = rnd(.9, 1.3), hgt = 5.5 * s;
    const trunk = new THREE.Mesh(new THREE.CylinderGeometry(.15 * s, .25 * s, hgt, 8), M.bark);
    trunk.position.set(x, hgt / 2, z); trunk.castShadow = true; add(trunk); blockers.push(trunk);
    for (let k = 0; k < 7; k++) {
      const leaf = new THREE.Mesh(new THREE.BoxGeometry(2.6 * s, .06, .6 * s), M.palm);
      const a = (k / 7) * Math.PI * 2;
      leaf.position.set(x + Math.cos(a) * 1.0 * s, hgt + .1, z + Math.sin(a) * 1.0 * s);
      leaf.rotation.y = -a; leaf.rotation.z = -.35; leaf.castShadow = true; add(leaf);
    }
    addCollider(x, z, .3 * s, .3 * s, 99);
  }
  function poleAt(x, z) {
    const m = new THREE.Mesh(new THREE.CylinderGeometry(.12, .15, 8, 8), M.pole);
    m.position.set(x, 4, z); m.castShadow = true; add(m); blockers.push(m);
    const l = new THREE.Mesh(new THREE.BoxGeometry(1.2, .15, .3), M.pole); l.position.set(x, 8, z); add(l);
    addCollider(x, z, .15, .15, 99);
  }
  function barrierAt(x, z, rot) { addBox(x, z, rot ? .8 : 3, 1.0, rot ? 3 : .8, M.concrete); }
  function containerAt(x, z, rot, mi) {
    reserve(x, z, 4);
    addBox(x, z, rot ? 2.4 : 6, 2.6, rot ? 6 : 2.4, M.cont[mi % 3]);
  }

  // ---- towers (stairs + roof for camping)
  function addTower(tx, tz, dir) {
    reserve(tx + dir * 4, tz, 11);
    addBox(tx, tz, 8, 5.5, 8, M.concrete);
    addBox(tx, tz, 8.2, .4, 8.2, M.dark, 5.1, true, true);
    for (let j = 0; j < 10; j++) addBox(tx + dir * (4 + .45 + .9 * j), tz, .9, 5.0 - 0.5 * j, 3, M.concrete);
    const t = .4, ph = 1.0;
    addBox(tx, tz - 3.8, 8, ph, t, houseMat, 5.5);
    addBox(tx, tz + 3.8, 8, ph, t, houseMat, 5.5);
    addBox(tx - dir * 3.8, tz, t, ph, 8, houseMat, 5.5);
    addBox(tx + dir * 3.8, tz - 2.75, t, ph, 2.5, houseMat, 5.5);
    addBox(tx + dir * 3.8, tz + 2.75, t, ph, 2.5, houseMat, 5.5);
    addBox(tx - dir * 2, tz + 1.5, 1.2, 1.1, 1.2, M.crate, 5.5);
  }

  // ---- enterable houses (doors, windows, roof, interior floor)
  function buildWall(cx, cz, horiz, len, t, h, mat, openings) {
    openings.sort((a, b) => a.pos - b.pos);
    let cur = -len / 2;
    const place = (a, b, hh, y0) => {
      const L = b - a; if (L < .05) return;
      const mid = (a + b) / 2;
      if (horiz) addBox(cx + mid, cz, L, hh, t, mat, y0); else addBox(cx, cz + mid, t, hh, L, mat, y0);
    };
    for (const o of openings) {
      place(cur, o.pos - o.w / 2, h, 0);
      if (o.type === "win") {
        if (horiz) { addBox(cx + o.pos, cz, o.w, 1.0, t, mat); addBox(cx + o.pos, cz, o.w, h - 2.3, t, mat, 2.3); }
        else { addBox(cx, cz + o.pos, t, 1.0, o.w, mat); addBox(cx, cz + o.pos, t, h - 2.3, o.w, mat, 2.3); }
      }
      cur = o.pos + o.w / 2;
    }
    place(cur, len / 2, h, 0);
  }
  function addHouse(cx, cz, w, d, o = {}) {
    const h = o.h || 3.2, t = .4, mat = o.mat || houseMat, doorW = o.doorW || 2.2;
    const doors = o.doors || [{ side: o.door || "S", off: 0 }];
    reserve(cx, cz, Math.max(w, d) / 2 + 2);
    const sides = {
      N: { horiz: true, len: w, x: cx, z: cz - d / 2 }, S: { horiz: true, len: w, x: cx, z: cz + d / 2 },
      W: { horiz: false, len: d, x: cx - w / 2, z: cz }, E: { horiz: false, len: d, x: cx + w / 2, z: cz }
    };
    for (const k of ["N", "S", "W", "E"]) {
      const sd = sides[k], op = [];
      const myDoors = doors.filter(dd => dd.side === k);
      myDoors.forEach(dd => op.push({ pos: dd.off || 0, w: doorW, type: "door" }));
      if (o.win !== false && sd.len >= 7) {
        [-sd.len / 3, sd.len / 3].forEach(p => {
          if (myDoors.every(dd => Math.abs(p - (dd.off || 0)) > doorW / 2 + 1.6)) op.push({ pos: p, w: 1.6, type: "win" });
        });
      }
      buildWall(sd.x, sd.z, sd.horiz, sd.horiz ? sd.len : sd.len - t, t, h, mat, op);
    }
    // interior floor
    const fl = new THREE.Mesh(new THREE.PlaneGeometry(w - t * 2, d - t * 2), M.floor);
    fl.rotation.x = -Math.PI / 2; fl.position.set(cx, .03, cz); fl.receiveShadow = true; add(fl);
    // roof (mesh only, no collider so you can walk inside)
    if (o.roof !== false) {
      const r = new THREE.Mesh(new THREE.BoxGeometry(w + .8, .35, d + .8), M.roof);
      r.position.set(cx, h + .17, cz); r.castShadow = r.receiveShadow = true; add(r); blockers.push(r);
    }
    // interior partition
    if (o.rooms) buildWall(cx + w / 6, cz, false, d - t, .3, h, mat, [{ pos: -d / 4, w: 1.8, type: "door" }]);
    // interior clutter
    const nCl = o.clutter == null ? 2 : o.clutter;
    for (let i = 0; i < nCl; i++) {
      const x = cx + rnd(-w / 2 + 1.6, w / 2 - 1.6), z = cz + rnd(-d / 2 + 1.6, d / 2 - 1.6);
      if (o.rooms && Math.abs(x - (cx + w / 6)) < 1.2) continue;
      if (Math.random() < .5) addBox(x, z, 1.1, 1.1, 1.1, M.crate); else addBox(x, z, 1.8, .9, .9, M.wood);
    }
  }

  function clearMap() {
    mapObjs.forEach(o => { scene.remove(o); if (o.geometry) o.geometry.dispose(); });
    mapObjs.length = 0; boxes.length = 0; blockers.length = 0; placed.length = 0;
    snowPts = null;
  }

  // ---- per-map layouts
  const MAPS = [
    () => { // Forest Outpost
      addTower(28, -26, 1); addTower(-30, 24, -1);
      addHouse(-22, -20, 12, 9, { door: "E", rooms: true });
      addHouse(24, 22, 9, 8, { door: "W" });
      containerAt(8, -34, 0, 0); containerAt(-12, 36, 0, 1); containerAt(36, 20, 1, 2); containerAt(-38, -4, 1, 0); containerAt(14, 28, 0, 1);
      addBox(0, -12, 12, 3, 1.4, M.concrete); addBox(-15, 5, 1.4, 3, 10, M.concrete); addBox(15, 5, 1.4, 3, 10, M.concrete); addBox(0, 14, 8, 3, 1.4, M.concrete);
      reserve(0, -12, 7); reserve(-15, 5, 6); reserve(15, 5, 6); reserve(0, 14, 5);
      scatter(16, 2, crateAt); scatter(12, 1.2, barrelAt); scatter(5, 3, sandAt);
      scatter(14, 2, (x, z) => rockAt(x, z, M.rock));
      scatter(80, 1.5, (x, z) => treeAt(x, z, rnd(.9, 1.5), "mixed"));
      for (let i = 0; i < 40; i++) { const m = new THREE.Mesh(new THREE.IcosahedronGeometry(rnd(.5, .9), 1), M.bush); m.position.set(rnd(-48, 48), .35, rnd(-48, 48)); m.scale.y = .7; add(m); }
    },
    () => { // Desert Town
      addTower(-26, -6, 1); addTower(26, 4, -1);
      addHouse(-34, -30, 10, 8, { door: "S" }); addHouse(-14, -33, 9, 8, { door: "S" });
      addHouse(14, -32, 10, 8, { door: "S", rooms: true }); addHouse(34, -14, 8, 10, { door: "W" });
      addHouse(36, 14, 8, 10, { door: "W" }); addHouse(-34, 12, 8, 10, { door: "E" });
      addHouse(-8, 32, 12, 9, { door: "N", rooms: true }); addHouse(16, 34, 9, 8, { door: "N" });
      for (const [x, z, w, d] of [[-18, 14, 6, 1], [18, -12, 1, 6], [4, 22, 5, 1], [-6, -20, 1, 5]]) addBox(x, z, w, 2.2, d, M.adobe);
      scatter(12, 2, crateAt); scatter(10, 1.2, barrelAt); scatter(4, 3, sandAt);
      scatter(8, 2, (x, z) => rockAt(x, z, M.rock)); scatter(18, 1.5, palmAt);
    },
    () => { // Industrial Yard
      addHouse(-22, -28, 22, 14, { h: 5, doors: [{ side: "S", off: -6 }, { side: "S", off: 6 }, { side: "E", off: 0 }], doorW: 3.4, clutter: 7, mat: M.metal });
      addHouse(26, -30, 10, 8, { door: "S", mat: M.concrete });
      addHouse(30, 26, 10, 8, { door: "W", mat: M.concrete, rooms: true });
      addTower(-34, 22, -1); addTower(38, 2, -1);
      [[-12, -6, 0, 0], [12, -8, 0, 1], [-4, 14, 1, 2], [8, 18, 0, 0], [0, -18, 0, 1], [-26, 2, 1, 2], [20, -18, 1, 0], [-14, 38, 0, 1], [10, 40, 0, 2], [42, -36, 1, 1], [-42, -6, 1, 0], [44, 34, 0, 2]]
        .forEach(([x, z, r, m]) => containerAt(x, z, r, m));
      for (let i = 0; i < 9; i++) { const x = rnd(-44, 44), z = rnd(-44, 44); if (free(x, z, 2)) { reserve(x, z, 2); barrierAt(x, z, Math.random() < .5); } }
      scatter(8, 3, poleAt); scatter(12, 2, crateAt); scatter(16, 1.2, barrelAt);
    },
    () => { // Snow Base
      addTower(26, 26, -1); addTower(-34, -2, 1);
      addHouse(-26, -26, 12, 9, { door: "E", rooms: true }); addHouse(28, -24, 9, 8, { door: "W" });
      addHouse(-30, 30, 9, 8, { door: "E" }); addHouse(14, 36, 10, 8, { door: "N", rooms: true });
      containerAt(0, -34, 0, 1); containerAt(40, 4, 1, 2);
      addBox(-8, 16, 8, 2.6, 1.2, M.concrete); addBox(10, -14, 1.2, 2.6, 8, M.concrete); reserve(-8, 16, 5); reserve(10, -14, 5);
      scatter(12, 2, crateAt); scatter(10, 1.2, barrelAt); scatter(6, 3, sandAt);
      scatter(14, 2, (x, z) => rockAt(x, z, M.snowrock));
      scatter(75, 1.5, (x, z) => treeAt(x, z, rnd(.9, 1.5), "snowpine"));
      // snowfall
      const N = 900, pos = new Float32Array(N * 3);
      for (let i = 0; i < N; i++) { pos[i * 3] = rnd(-30, 30); pos[i * 3 + 1] = rnd(0, 25); pos[i * 3 + 2] = rnd(-30, 30); }
      const g = new THREE.BufferGeometry(); g.setAttribute("position", new THREE.BufferAttribute(pos, 3));
      snowPts = add(new THREE.Points(g, new THREE.PointsMaterial({ color: 0xffffff, size: .12, transparent: true, opacity: .9 })));
      snowPts.frustumCulled = false;
    }
  ];

  let currentMap = -1;
  function buildMap(idx) {
    clearMap();
    currentMap = idx; theme = THEMES[idx];
    houseMat = M[theme.house]; wallMat = M[theme.wall];
    scene.fog.color.setHex(theme.fog); scene.fog.near = theme.fogN; scene.fog.far = theme.fogF;
    skyMesh.material.map = skyTex(theme);
    floor.material.map = groundTex(theme.ground);
    hemi.color.setHex(theme.hemiS); hemi.groundColor.setHex(theme.hemiG); sun.color.setHex(theme.sun);
    placed.push({ x: 0, z: 0, r: 7 });
    // perimeter wall
    for (const [x, z, w, d] of [[0, -ARENA - .5, ARENA * 2 + 2, 1], [0, ARENA + .5, ARENA * 2 + 2, 1], [-ARENA - .5, 0, 1, ARENA * 2], [ARENA + .5, 0, 1, ARENA * 2]])
      addBox(x, z, w, 4, d, wallMat);
    // distant mountains
    const mtnMat = new THREE.MeshStandardMaterial({ color: theme.mtn, flatShading: true });
    for (let i = 0; i < 28; i++) {
      const a = (i / 28) * Math.PI * 2, r = 130 + Math.random() * 30, h = rnd(25, 55);
      const m = new THREE.Mesh(new THREE.ConeGeometry(rnd(22, 36), h, 6), mtnMat);
      m.position.set(Math.cos(a) * r, h / 2 - 2, Math.sin(a) * r); add(m);
    }
    MAPS[idx]();
    // trees outside the wall
    if (theme.outer !== "none") {
      for (let i = 0; i < 70; i++) {
        const a = Math.random() * Math.PI * 2, r = rnd(ARENA + 5, ARENA + 60);
        const x = Math.cos(a) * r, z = Math.sin(a) * r;
        if (Math.abs(x) < ARENA + 3 && Math.abs(z) < ARENA + 3) continue;
        const s = rnd(1.2, 2.2);
        const t = new THREE.Mesh(new THREE.CylinderGeometry(.2 * s, .3 * s, 3 * s, 6), M.bark); t.position.set(x, 1.5 * s, z); add(t);
        const c = new THREE.Mesh(new THREE.ConeGeometry(2 * s, 5 * s, 8), theme.outer === "snowpine" ? M.snowpine : M.pine); c.position.set(x, 5 * s, z); add(c);
      }
    }
    $("mapname").textContent = theme.name;
  }

  // ====================== SOUND ======================
  const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  const noiseBuf = (() => { const b = audioCtx.createBuffer(1, audioCtx.sampleRate, audioCtx.sampleRate), d = b.getChannelData(0); for (let i = 0; i < d.length; i++) d[i] = Math.random() * 2 - 1; return b; })();
  function boom(freq, dur, vol, thump) {
    if (audioCtx.state !== "running" || vol <= 0.002) return;
    const t = audioCtx.currentTime;
    const s = audioCtx.createBufferSource(); s.buffer = noiseBuf;
    const f = audioCtx.createBiquadFilter(); f.type = "lowpass"; f.frequency.value = freq;
    const g = audioCtx.createGain(); g.gain.setValueAtTime(vol, t); g.gain.exponentialRampToValueAtTime(0.001, t + dur);
    s.connect(f); f.connect(g); g.connect(audioCtx.destination); s.start(t, Math.random() * .4, Math.min(dur, .55));
    if (thump) {
      const o = audioCtx.createOscillator(), og = audioCtx.createGain();
      o.frequency.setValueAtTime(140, t); o.frequency.exponentialRampToValueAtTime(40, t + dur);
      og.gain.setValueAtTime(vol * 1.2, t); og.gain.exponentialRampToValueAtTime(0.001, t + dur);
      o.connect(og); og.connect(audioCtx.destination); o.start(t); o.stop(t + dur);
    }
  }
  function click(freq = 900, vol = .06) {
    if (audioCtx.state !== "running") return;
    const t = audioCtx.currentTime, o = audioCtx.createOscillator(), g = audioCtx.createGain();
    o.type = "square"; o.frequency.value = freq; g.gain.setValueAtTime(vol, t); g.gain.exponentialRampToValueAtTime(0.001, t + .05);
    o.connect(g); g.connect(audioCtx.destination); o.start(t); o.stop(t + .06);
  }

  // ====================== WEAPONS ======================
  const W = [
    { name: "Pistol",     mag: 12, reserve: 60,  dmg: 30,  rate: .24, auto: false, reload: 1.1, spread: .006, pellets: 1, kick: .012, zoom: 62, snd: [3500, .10, 0] },
    { name: "AK-47",      mag: 30, reserve: 120, dmg: 24,  rate: .10, auto: true,  reload: 1.9, spread: .014, pellets: 1, kick: .008, zoom: 58, snd: [2600, .14, 0] },
    { name: "Shotgun",    mag: 8,  reserve: 32,  dmg: 15,  rate: .85, auto: false, reload: 2.3, spread: .055, pellets: 8, kick: .05,  zoom: 68, snd: [1500, .30, 1] },
    { name: "Sniper AWP", mag: 5,  reserve: 20,  dmg: 150, rate: 1.5, auto: false, reload: 2.8, spread: .05,  pellets: 1, kick: .06,  zoom: 12, snd: [1100, .55, 1] },
    { name: "Knife",      mag: 0,  reserve: 0,   dmg: 60,  rate: .55, auto: false, reload: 0,   spread: 0,    pellets: 0, kick: 0,    zoom: 75, snd: [0, 0, 0], melee: true, range: 2.5 }
  ];
  const NW = W.length;

  const vmRoot = new THREE.Group();
  vmRoot.position.set(.22, -.24, -.45);
  camera.add(vmRoot);
  function vbox(parent, w, h, d, color, x, y, z, rx = 0, metal = .4) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color, roughness: .55, metalness: metal }));
    m.position.set(x, y, z); m.rotation.x = rx; parent.add(m); return m;
  }
  function vcyl(parent, r, len, color, x, y, z) {
    const m = new THREE.Mesh(new THREE.CylinderGeometry(r, r, len, 10), new THREE.MeshStandardMaterial({ color, roughness: .4, metalness: .6 }));
    m.rotation.x = Math.PI / 2; m.position.set(x, y, z); parent.add(m); return m;
  }
  const VM = W.map(() => new THREE.Group());
  const muzzleZ = [-.3, -.88, -.95, -1.0, -.4];
  { const g = VM[0]; vbox(g, .06, .09, .32, 0x2a2a2a, 0, 0, -.1); vbox(g, .055, .16, .09, 0x1a1a1a, 0, -.12, .02, .2); }
  { const g = VM[1];
    vbox(g, .07, .1, .7, 0x2c2c2c, 0, 0, -.05); vbox(g, .08, .08, .28, 0x6b4423, 0, -.01, -.5, 0, .1);
    vcyl(g, .017, .35, 0x111111, 0, .02, -.7); vbox(g, .06, .13, .3, 0x6b4423, 0, -.04, .42, 0, .1);
    vbox(g, .05, .22, .09, 0x222222, 0, -.15, -.12, .35); vbox(g, .03, .04, .03, 0x111111, 0, .075, -.62); }
  { const g = VM[2];
    vcyl(g, .026, .95, 0x1c1c1c, 0, .02, -.45); vcyl(g, .022, .7, 0x1c1c1c, 0, -.03, -.35);
    vbox(g, .085, .07, .24, 0x6b4423, 0, -.05, -.42, 0, .1); vbox(g, .07, .1, .32, 0x2a2a2a, 0, 0, .05);
    vbox(g, .06, .13, .32, 0x6b4423, 0, -.05, .4, 0, .1); }
  { const g = VM[3];
    vbox(g, .06, .09, .85, 0x2f3b2a, 0, 0, 0, 0, .2); vcyl(g, .018, .7, 0x111111, 0, .02, -.7);
    vcyl(g, .038, .34, 0x111111, 0, .11, -.1); vcyl(g, .05, .06, 0x222222, 0, .11, -.28); vcyl(g, .045, .05, 0x222222, 0, .11, .08);
    vbox(g, .06, .13, .42, 0x2f3b2a, 0, -.04, .5, 0, .2); vbox(g, .05, .12, .08, 0x222222, 0, -.1, -.05, .2); }
  { const g = VM[4];
    vbox(g, .02, .07, .34, 0xd0d6dc, 0, .02, -.32, 0, .9); vbox(g, .02, .12, .03, 0x333333, 0, 0, -.14); vbox(g, .035, .045, .16, 0x1c1c1c, 0, 0, -.04);
    g.rotation.set(.25, 0, .15); }
  const vmFlash = [];
  VM.forEach((g, i) => {
    const f = new THREE.Mesh(new THREE.SphereGeometry(.06, 8, 6), new THREE.MeshBasicMaterial({ color: 0xffd060 }));
    f.position.set(0, 0, muzzleZ[i]); f.visible = false; g.add(f); vmFlash.push(f);
    g.visible = false; vmRoot.add(g);
  });
  vbox(vmRoot, .09, .09, .12, 0xd9a77e, 0, -.09, -.18, 0, 0);
  vbox(vmRoot, .11, .11, .5, 0x3a4a30, .03, -.2, .15, -.25, 0);
  const muzzleLight = new THREE.PointLight(0xffaa44, 0, 8); muzzleLight.position.set(.22, -.15, -1.2); camera.add(muzzleLight);
  const blastLight = new THREE.PointLight(0xff9a40, 0, 30); scene.add(blastLight);

  // ====================== STATE ======================
  const player = {
    pos: new V3(0, 0, 0), vy: 0, onGround: true, hp: 100, kills: 0, alive: false,
    yaw: 0, pitch: 0, camY: 1.7, wi: 1, prevWi: 1, scoped: false, reloadT: 0, fireCd: 0, recoil: 0, hurtT: 0, bob: 0, swing: 0, throwT: 0,
    shake: 0, knifeRevert: 0, nades: 3, smokes: 2, flashT: 0,
    ws: W.map(w => ({ ammo: w.mag, reserve: w.reserve }))
  };
  const keys = {};
  let wave = 1, toSpawn = 0, spawnT = 0, betweenWaves = false, waveTimer = 0, allyRespawn = 0, ally = null;
  let selMap = 0, locked = false, wasLocked = false, mouseX = innerWidth / 2, mouseY = innerHeight / 2;
  let firing = false, rightDown = false, touchScope = false;
  let touchMode = matchMedia("(pointer: coarse)").matches;
  const joy = { id: null, ox: 0, oy: 0, x: 0, y: 0 };
  let lookId = null, lookX = 0, lookY = 0;
  const menu = $("menu"), msg = $("msg");
  const gameRunning = () => player.alive && menu.style.display === "none";

  function setWeapon(i) {
    player.wi = i; player.reloadT = 0; player.fireCd = .3; touchScope = false; $("tAim").classList.remove("on");
    VM.forEach((g, k) => (g.visible = k === i));
    updateHUD();
  }
  function startReload() {
    const w = W[player.wi], st = player.ws[player.wi];
    if (w.melee || player.reloadT > 0 || st.ammo >= w.mag || st.reserve <= 0) return;
    player.reloadT = w.reload; touchScope = false; $("tAim").classList.remove("on"); click(500, .08);
  }

  // ====================== HUD ======================
  const slotsEl = $("weapons");
  W.forEach((w, i) => {
    const d = document.createElement("div"); d.className = "wslot"; d.id = "slot" + i; d.textContent = (i + 1) + " " + w.name;
    const pick = e => { e.stopPropagation(); if (gameRunning()) setWeapon(i); };
    d.addEventListener("click", pick); d.addEventListener("touchstart", e => { e.preventDefault(); pick(e); }, { passive: false });
    slotsEl.appendChild(d);
  });
  let bannerT = 0;
  function banner(text, sec = 1.6) { const b = $("banner"); b.textContent = text; b.style.opacity = 1; bannerT = sec; }
  function updateHUD() {
    const w = W[player.wi], st = player.ws[player.wi];
    $("health").textContent = "HP: " + Math.max(0, Math.ceil(player.hp));
    $("hpfill").style.width = Math.max(0, player.hp) + "%";
    $("hpfill").style.background = player.hp > 50 ? "#4c4" : player.hp > 25 ? "#ec4" : "#e33";
    $("wname").textContent = w.name;
    $("ammo").textContent = w.melee ? "--" : player.reloadT > 0 ? "Reloading..." : st.ammo + " / " + st.reserve;
    $("nades").textContent = "Frag (G): " + player.nades + "   Smoke (H): " + player.smokes;
    $("score").textContent = "Kills: " + player.kills;
    $("wave").textContent = "Wave: " + wave;
    W.forEach((_, i) => $("slot" + i).classList.toggle("active", i === player.wi));
    $("allybox").textContent = ally ? "Ally HP: " + Math.max(0, Math.ceil(ally.hp)) : "Ally: respawning...";
    $("allybox").style.color = "#7cf";
  }
  let hmT = 0;
  function hitMarker(kill) { const h = $("hitmarker"); h.className = kill ? "kill" : ""; h.style.opacity = 1; hmT = .15; }

  // ====================== EFFECTS ======================
  const pGeo = new THREE.BoxGeometry(1, 1, 1);
  const pMats = {
    blood: new THREE.MeshBasicMaterial({ color: 0x8a0b0b }),
    spark: new THREE.MeshBasicMaterial({ color: 0xffd070 }),
    dust: new THREE.MeshBasicMaterial({ color: 0xa8a090 }),
    fire: new THREE.MeshBasicMaterial({ color: 0xff7a1a }),
    smoke: new THREE.MeshBasicMaterial({ color: 0x555555 })
  };
  const particles = [], decals = [], tracers = [], fx = [], grenades = [], smokes = [];
  const circleGeo = new THREE.CircleGeometry(1, 14);
  const decalMat = new THREE.MeshBasicMaterial({ color: 0x4a0505, transparent: true, opacity: .78, depthWrite: false, polygonOffset: true, polygonOffsetFactor: -2 });
  const scorchMat = new THREE.MeshBasicMaterial({ color: 0x111111, transparent: true, opacity: .6, depthWrite: false, polygonOffset: true, polygonOffsetFactor: -2 });
  function addDecal(x, z, y, r, mat = decalMat) {
    const m = new THREE.Mesh(circleGeo, mat);
    m.rotation.x = -Math.PI / 2; m.position.set(x, y + .03, z); m.scale.set(r, r, r); scene.add(m);
    decals.push(m); if (decals.length > 70) scene.remove(decals.shift());
    return m;
  }
  function emit(pos, n, kind, speed, dir, size, life) {
    for (let i = 0; i < n && particles.length < 340; i++) {
      const m = new THREE.Mesh(pGeo, pMats[kind]); const sz = size * rnd(.6, 1.4);
      m.scale.set(sz, sz, sz); m.position.copy(pos);
      const v = new V3(rnd(-1, 1), rnd(-.2, 1.2), rnd(-1, 1)).multiplyScalar(speed);
      if (dir) v.addScaledVector(dir, speed * .8);
      scene.add(m); particles.push({ m, v, life: life * rnd(.7, 1.3), kind, rest: false });
    }
  }
  function addTracer(a, b, color = 0xffe9a0) {
    const l = new THREE.Line(new THREE.BufferGeometry().setFromPoints([a.clone(), b.clone()]), new THREE.LineBasicMaterial({ color, transparent: true, opacity: .9 }));
    scene.add(l); tracers.push({ l, t: .07 });
  }

  // ====================== SMOKE + GRENADES ======================
  const smokeTex = makeTex(64, (g, s) => {
    const gr = g.createRadialGradient(s / 2, s / 2, 2, s / 2, s / 2, s / 2);
    gr.addColorStop(0, "rgba(235,235,235,.95)"); gr.addColorStop(.6, "rgba(215,215,215,.55)"); gr.addColorStop(1, "rgba(200,200,200,0)");
    g.fillStyle = gr; g.fillRect(0, 0, s, s);
  });
  function deploySmoke(pos) {
    boom(1800, 1.2, .08, 0);
    const S = { pos: pos.clone(), r: 4.6, t: 0, life: 17, puffs: [] };
    for (let i = 0; i < 30; i++) {
      const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: smokeTex, color: 0xe2e2e2, transparent: true, opacity: 0, depthWrite: false }));
      const a = Math.random() * Math.PI * 2, rr = Math.random() * S.r * .7;
      sp.position.set(pos.x + Math.cos(a) * rr, pos.y + rnd(.4, 3.4), pos.z + Math.sin(a) * rr);
      sp.userData.target = rnd(4.5, 7); sp.raycast = () => {};
      sp.scale.set(.5, .5, 1); scene.add(sp); S.puffs.push(sp);
    }
    smokes.push(S);
  }
  function smokeBlocks(a, b) {
    for (const S of smokes) {
      if (S.t < .8) continue;
      const c = tmpC.set(S.pos.x, S.pos.y + 1.6, S.pos.z), R = S.r * .8;
      const ab = tmpD.copy(b).sub(a), l2 = ab.lengthSq();
      let t = l2 > 0 ? clamp(tmpE.copy(c).sub(a).dot(ab) / l2, 0, 1) : 0;
      const q = tmpE.copy(a).addScaledVector(ab, t);
      if (q.distanceTo(c) < R) return true;
    }
    return false;
  }
  function updateSmokes(dt) {
    for (let i = smokes.length - 1; i >= 0; i--) {
      const S = smokes[i]; S.t += dt;
      const grow = Math.min(S.t / 2, 1), fade = S.t > S.life - 4 ? Math.max(0, (S.life - S.t) / 4) : 1;
      S.puffs.forEach(sp => { const sc = sp.userData.target * (.3 + .7 * grow); sp.scale.set(sc, sc, 1); sp.material.opacity = .85 * grow * fade; sp.position.y += dt * .08; });
      if (S.t >= S.life) { S.puffs.forEach(sp => { scene.remove(sp); sp.material.dispose(); }); smokes.splice(i, 1); }
    }
  }

  const grenGeo = new THREE.SphereGeometry(.09, 10, 8);
  const grenMat = { frag: new THREE.MeshStandardMaterial({ color: 0x3d5a2a, roughness: .5, metalness: .4 }), smoke: new THREE.MeshStandardMaterial({ color: 0x9aa0a6, roughness: .5, metalness: .4 }) };
  function throwGrenade(type) {
    if (!gameRunning() || player.throwT > 0) return;
    if (type === "frag") { if (player.nades <= 0) return; player.nades--; } else { if (player.smokes <= 0) return; player.smokes--; }
    player.throwT = .55; player.reloadT = 0;
    const dir = new V3(0, 0, -1).applyQuaternion(camera.quaternion).normalize();
    const m = new THREE.Mesh(grenGeo, grenMat[type]); m.castShadow = true;
    const pos = camera.position.clone().addScaledVector(dir, .6); pos.y -= .15;
    m.position.copy(pos); scene.add(m);
    grenades.push({ type, m, pos, vel: dir.clone().multiplyScalar(17).add(new V3(0, 3.2, 0)), t: 0 });
    click(400, .08); updateHUD();
  }
  function explode(pos) {
    boom(300, .9, .45, 1);
    blastLight.position.copy(pos); blastLight.position.y += 1; blastLight.intensity = 8;
    emit(pos, 40, "fire", 7, null, .32, .55); emit(pos, 26, "smoke", 3.5, null, .45, 1.6); emit(pos, 22, "spark", 10, null, .08, .7); emit(pos, 18, "dust", 5, null, .3, 1.2);
    const sw = new THREE.Mesh(new THREE.SphereGeometry(1, 16, 12), new THREE.MeshBasicMaterial({ color: 0xffc070, transparent: true, opacity: .55, depthWrite: false }));
    sw.position.copy(pos); scene.add(sw); fx.push({ m: sw, t: 0, dur: .35 });
    const gy = groundAt(pos.x, pos.z, pos.y);
    if (pos.y - gy < 2) addDecal(pos.x, pos.z, gy, 1.8, scorchMat);
    const RAD = 8;
    for (const s of soldiers.slice()) {
      if (s.dead) continue;
      const c = new V3(s.group.position.x, s.group.position.y + 1.1, s.group.position.z);
      const d = c.distanceTo(pos); if (d > RAD) continue;
      const los = hasLOS(pos.clone().add(new V3(0, .3, 0)), c, true);
      const dmg = (1 - d / RAD) * 190 * (los ? 1 : .35);
      if (dmg > 8) hurtSoldier(s, dmg, "body", c, c.clone().sub(pos).normalize(), true);
    }
    if (player.alive) {
      const c = new V3(player.pos.x, player.pos.y + 1.2, player.pos.z), d = c.distanceTo(pos);
      if (d < RAD) { const los = hasLOS(pos.clone().add(new V3(0, .3, 0)), c, true); hurtPlayer((1 - d / RAD) * 85 * (los ? 1 : .35)); }
      player.shake = Math.max(player.shake, clamp(1 - d / 28, 0, 1) * .12);
    }
  }
  function updateGrenades(dt) {
    for (let i = grenades.length - 1; i >= 0; i--) {
      const g = grenades[i]; g.t += dt;
      g.vel.y -= 18 * dt;
      const old = g.pos.clone(); g.pos.addScaledVector(g.vel, dt);
      const R = .1;
      for (const b of boxes) {
        if (g.pos.x > b.minX - R && g.pos.x < b.maxX + R && g.pos.z > b.minZ - R && g.pos.z < b.maxZ + R && g.pos.y < b.top + R) {
          if (old.y >= b.top) { g.pos.y = b.top + R; if (g.vel.y < 0) g.vel.y *= -.35; g.vel.x *= .7; g.vel.z *= .7; }
          else if (old.x <= b.minX - R || old.x >= b.maxX + R) { g.pos.x = old.x; g.vel.x *= -.45; }
          else if (old.z <= b.minZ - R || old.z >= b.maxZ + R) { g.pos.z = old.z; g.vel.z *= -.45; }
          else { g.pos.y = b.top + R; g.vel.y = Math.abs(g.vel.y) * .3; }
        }
      }
      if (g.pos.y < R) { g.pos.y = R; if (g.vel.y < 0) g.vel.y *= -.4; g.vel.x *= .8; g.vel.z *= .8; if (Math.abs(g.vel.y) < 1) g.vel.y = 0; }
      g.pos.x = clamp(g.pos.x, -ARENA + 1, ARENA - 1); g.pos.z = clamp(g.pos.z, -ARENA + 1, ARENA - 1);
      g.m.position.copy(g.pos);
      if (g.type === "frag" && g.t >= 2.4) { scene.remove(g.m); grenades.splice(i, 1); explode(g.pos); }
      else if (g.type === "smoke" && g.t >= 1.4) { scene.remove(g.m); grenades.splice(i, 1); deploySmoke(g.pos); }
    }
    for (let i = fx.length - 1; i >= 0; i--) {
      const f = fx[i]; f.t += dt; const k = f.t / f.dur;
      const s = .3 + k * 8; f.m.scale.set(s, s, s); f.m.material.opacity = .55 * (1 - k);
      if (k >= 1) { scene.remove(f.m); f.m.geometry.dispose(); f.m.material.dispose(); fx.splice(i, 1); }
    }
    blastLight.intensity = Math.max(0, blastLight.intensity - dt * 25);
  }

  // ====================== SOLDIERS ======================
  const soldiers = [], corpses = [];
  const tmpA = new V3(), tmpB = new V3(), tmpC = new V3(), tmpD = new V3(), tmpE = new V3();

  function makeHuman(team) {
    const isAlly = team === "ally";
    const shirt = isAlly ? 0x2f5d8a : [0x7a2b2b, 0x5c3a1e, 0x6a2a2a][Math.floor(Math.random() * 3)];
    const pants = isAlly ? 0x2a3441 : 0x2b2b2b, vest = isAlly ? 0x1d3b57 : 0x1a1a1a, helmetC = isAlly ? 0x2a4a2a : 0x3a3a3a, skin = 0xd9a77e;
    const g = new THREE.Group(); g.rotation.order = "YXZ";
    const hit = [];
    const mk = (w, h, d, c) => { const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshStandardMaterial({ color: c, roughness: .85 })); m.castShadow = true; return m; };
    const part = (m, p) => { m.userData.part = p; hit.push(m); return m; };
    function leg(x) {
      const pv = new THREE.Group(); pv.position.set(x, .88, 0);
      const l = part(mk(.25, .88, .28, pants), "limb"); l.position.y = -.44;
      const b = mk(.27, .12, .38, 0x111111); b.position.set(0, -.84, .05);
      pv.add(l, b); g.add(pv); return pv;
    }
    const legL = leg(-.15), legR = leg(.15);
    const torso = part(mk(.6, .72, .32, shirt), "body"); torso.position.y = 1.25; g.add(torso);
    const vestM = part(mk(.64, .5, .36, vest), "body"); vestM.position.y = 1.3; g.add(vestM);
    const head = part(new THREE.Mesh(new THREE.SphereGeometry(.19, 14, 12), new THREE.MeshStandardMaterial({ color: skin, roughness: .8 })), "head");
    head.position.y = 1.82; head.castShadow = true; g.add(head);
    const hel = part(new THREE.Mesh(new THREE.SphereGeometry(.225, 14, 10, 0, Math.PI * 2, 0, Math.PI * .55), new THREE.MeshStandardMaterial({ color: helmetC, roughness: .6 })), "head");
    hel.position.y = 1.84; hel.castShadow = true; g.add(hel);
    function arm(x) {
      const pv = new THREE.Group(); pv.rotation.order = "YXZ"; pv.position.set(x, 1.55, 0);
      const a = part(mk(.15, .6, .15, shirt), "limb"); a.position.y = -.3;
      const h = mk(.1, .1, .1, skin); h.position.y = -.64; pv.add(a, h); g.add(pv); return pv;
    }
    const armR = arm(.4), armL = arm(-.4);
    armR.rotation.set(-1.4, -.5, 0); armL.rotation.set(-1.3, .5, 0);
    const gun = new THREE.Group(); gun.position.set(0, 1.42, .35);
    const gb = mk(.07, .1, .8, 0x222222), gs = mk(.06, .1, .28, 0x5a3a1a), gbar = mk(.03, .03, .3, 0x111111), gm = mk(.05, .18, .08, 0x222222);
    gs.position.set(0, -.02, -.5); gbar.position.set(0, .02, .55); gm.position.set(0, -.13, .1);
    gun.add(gb, gs, gbar, gm); g.add(gun);
    const tip = new THREE.Object3D(); tip.position.set(0, 0, .72); gun.add(tip);
    const flash = new THREE.Mesh(new THREE.SphereGeometry(.12, 8, 6), new THREE.MeshBasicMaterial({ color: 0xffcc55 }));
    flash.position.set(0, 0, .8); flash.visible = false; flash.raycast = () => {}; gun.add(flash);
    return { group: g, legL, legR, armL, armR, torso, head, tip, flash, hit, gun };
  }
  function makeLabel(text, color) {
    const c = document.createElement("canvas"); c.width = 256; c.height = 64;
    const g = c.getContext("2d"); g.font = "bold 40px Arial"; g.textAlign = "center"; g.lineWidth = 6; g.strokeStyle = "#000"; g.fillStyle = color;
    g.strokeText(text, 128, 46); g.fillText(text, 128, 46);
    const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: new THREE.CanvasTexture(c), depthTest: false, transparent: true }));
    s.scale.set(1.6, .4, 1); s.renderOrder = 10; s.raycast = () => {}; return s;
  }

  function spawnSoldier(team, x, z) {
    const h = makeHuman(team);
    const s = Object.assign(h, {
      team, hp: 100, dead: false, target: null, tgtT: 0, losT: 0, hasLOS: false, seenT: 0, fireT: rnd(.5, 1.2),
      speed: team === "enemy" ? clamp(2.8 + wave * .15 + rnd(0, .8), 2.8, 4.6) : 4.2, pref: rnd(10, 24),
      side: 1, sideT: 0, strafeT: 0, strafeDir: 1, still: false, lx: x, lz: z, stuckT: 0, walkPh: Math.random() * 6, flashT: 0, muzzleT: 0
    });
    s.group.position.set(x, groundAt(x, z, 0), z);
    s.hit.forEach(m => (m.userData.soldier = s));
    if (team === "ally") { s.label = makeLabel("ALLY", "#6cf"); s.label.position.set(0, 2.5, 0); s.group.add(s.label); s.pref = 14; }
    scene.add(s.group); soldiers.push(s);
    return s;
  }

  const losRay = new THREE.Raycaster();
  function hasLOS(from, to, ignoreSmoke) {
    const d = tmpB.copy(to).sub(from), dist = d.length(); d.normalize();
    losRay.set(from, d); losRay.far = Math.max(0.1, dist - 0.6);
    if (losRay.intersectObjects(blockers, false).length) return false;
    return ignoreSmoke ? true : !smokeBlocks(from, to);
  }
  function eyeOf(t, out) {
    if (t === "player") return out.set(player.pos.x, player.pos.y + 1.6, player.pos.z);
    return out.set(t.group.position.x, t.group.position.y + 1.5, t.group.position.z);
  }
  function pickTarget(s) {
    const p = s.group.position;
    if (s.team === "enemy") {
      let best = null, bd = 1e9;
      if (player.alive) { bd = Math.hypot(player.pos.x - p.x, player.pos.z - p.z); best = "player"; }
      if (ally && !ally.dead) { const d = Math.hypot(ally.group.position.x - p.x, ally.group.position.z - p.z); if (d < bd) { best = ally; bd = d; } }
      return best;
    }
    let best = null, bd = 60;
    for (const e of soldiers) if (e.team === "enemy" && !e.dead) { const d = Math.hypot(e.group.position.x - p.x, e.group.position.z - p.z); if (d < bd) { bd = d; best = e; } }
    return best;
  }

  function hurtPlayer(dmg) {
    if (!player.alive || dmg <= 0) return;
    player.hp -= dmg; player.hurtT = .35; boom(500, .12, .08, 0); updateHUD();
    if (player.hp <= 0) die();
  }
  function hurtSoldier(s, dmg, part, point, dir, byPlayer) {
    if (s.dead) return;
    s.hp -= dmg; s.flashT = .1;
    emit(point, part === "head" ? 14 : 8, "blood", 2.2, dir, .07, 2.5);
    if (s.team === "enemy" && byPlayer) hitMarker(s.hp <= 0);
    if (s.hp <= 0) killSoldier(s, part === "head", byPlayer, dir);
  }
  function killSoldier(s, head, byPlayer, dir) {
    s.dead = true;
    const i = soldiers.indexOf(s); if (i >= 0) soldiers.splice(i, 1);
    if (s.label) s.group.remove(s.label);
    s.flash.visible = false; s.torso.material.emissive.setHex(0);
    const p = s.group.position;
    const gy = groundAt(p.x, p.z, p.y);
    const pool = addDecal(p.x, p.z, gy, .2);
    const slide = dir ? new V3(dir.x, 0, dir.z) : new V3(0, 0, -1);
    if (slide.lengthSq() < 1e-4) slide.set(0, 0, -1); slide.normalize();
    corpses.push({ s, t: 0, pool, slide, roll: rnd(-.4, .4) });
    emit(new V3(p.x, p.y + 1.3, p.z), head ? 26 : 16, "blood", 3, dir, .09, 3);
    if (s.team === "enemy") {
      player.kills++;
      if (byPlayer && head) banner("HEADSHOT!", 1.0);
    } else { ally = null; allyRespawn = 12; banner("Your teammate is down!", 1.5); }
    updateHUD();
  }

  function fireSoldier(s, tg, dist, te) {
    s.group.updateMatrixWorld(true);
    const mz = s.tip.getWorldPosition(new V3());
    const chance = clamp((s.team === "enemy" ? .55 : .62) - dist * .008, .1, .6);
    const hit = Math.random() < chance;
    const aim = te.clone(); aim.y -= .25;
    const end = hit ? aim : aim.clone().add(new V3(rnd(-1.4, 1.4), rnd(-.8, .8), rnd(-1.4, 1.4)));
    addTracer(mz, end, s.team === "enemy" ? 0xff9a5a : 0x8ad0ff);
    s.muzzleT = .05;
    const d = Math.hypot(mz.x - player.pos.x, mz.z - player.pos.z);
    boom(2400, .14, clamp(.5 / (d * .15 + 1), 0, .12), 0);
    if (!hit) return;
    if (tg === "player") hurtPlayer(rnd(5, 9));
    else if (s.team === "enemy") hurtSoldier(tg, rnd(8, 14), "body", aim, aim.clone().sub(mz).normalize(), false);
    else hurtSoldier(tg, rnd(20, 32), Math.random() < .12 ? "head" : "body", aim, aim.clone().sub(mz).normalize(), false);
  }

  function updateSoldier(s, dt) {
    const g = s.group, p = g.position;
    s.tgtT -= dt;
    if (s.tgtT <= 0) { s.tgtT = .5; s.target = pickTarget(s); }
    if (s.target && (s.target === "player" ? !player.alive : s.target.dead)) s.target = null;
    const tg = s.target;
    let mv = null, spd = 0;
    const se = tmpA.set(p.x, p.y + 1.6, p.z);

    if (tg) {
      const te = eyeOf(tg, new V3());
      const dx = te.x - p.x, dz = te.z - p.z, dist = Math.hypot(dx, dz) + 1e-6;
      g.rotation.y += wrapA(Math.atan2(dx, dz) - g.rotation.y) * Math.min(1, dt * (s.hasLOS ? 10 : 5));
      s.losT -= dt;
      if (s.losT <= 0) { s.losT = .25 + Math.random() * .1; s.hasLOS = dist < 85 && hasLOS(se.clone(), te); }
      if (s.hasLOS) s.seenT += dt; else s.seenT = 0;
      const toward = new V3(dx / dist, 0, dz / dist);
      if (s.hasLOS && dist <= s.pref) {
        s.strafeT -= dt;
        if (s.strafeT <= 0) { s.strafeT = rnd(.8, 2); s.strafeDir = Math.random() < .5 ? -1 : 1; s.still = Math.random() < .4; }
        if (!s.still) { mv = new V3(toward.z * s.strafeDir, 0, -toward.x * s.strafeDir); spd = 1.6; }
      } else { mv = toward.clone(); spd = s.speed; }
      if (s.sideT > 0 && mv && spd > 2) { s.sideT -= dt; mv = new V3(toward.z * s.side, 0, -toward.x * s.side).multiplyScalar(.9).addScaledVector(toward, .3); }
      if (s.hasLOS && s.seenT > .5 && dist < 80) {
        s.fireT -= dt;
        if (s.fireT <= 0) { s.fireT = rnd(.3, .8) * (s.team === "ally" ? .7 : 1); fireSoldier(s, tg, dist, te); }
      }
    } else if (s.team === "ally") {
      const dx = player.pos.x - p.x, dz = player.pos.z - p.z, dist = Math.hypot(dx, dz);
      if (dist > 6) {
        mv = new V3(dx / dist, 0, dz / dist); spd = dist > 15 ? 6 : 4.2;
        if (s.sideT > 0) { s.sideT -= dt; const f = mv.clone(); mv = new V3(f.z * s.side, 0, -f.x * s.side).addScaledVector(f, .3); }
      } else g.rotation.y += wrapA(player.yaw + Math.PI - g.rotation.y) * Math.min(1, dt * 2);
      s.hasLOS = false;
    } else s.hasLOS = false;

    s.stuckT += dt;
    if (s.stuckT >= .6) {
      s.stuckT = 0;
      if (mv && spd > 2 && Math.hypot(p.x - s.lx, p.z - s.lz) < .5 && s.sideT <= 0) { s.sideT = 2; s.side = Math.random() < .5 ? -1 : 1; }
      s.lx = p.x; s.lz = p.z;
    }
    if (mv) {
      mv.y = 0; mv.normalize();
      p.x += mv.x * spd * dt; p.z += mv.z * spd * dt;
      collide(p, p.y, .4);
      if (!tg || !s.hasLOS) g.rotation.y += wrapA(Math.atan2(mv.x, mv.z) - g.rotation.y) * Math.min(1, dt * 8);
    }
    const gr = groundAt(p.x, p.z, p.y);
    p.y = p.y < gr ? gr : Math.max(gr, p.y - 12 * dt);

    s.walkPh += spd * dt * 2.6;
    const m = Math.min(1, spd / 3);
    s.legL.rotation.x = Math.sin(s.walkPh) * .7 * m; s.legR.rotation.x = -Math.sin(s.walkPh) * .7 * m;
    s.torso.position.y = 1.25 + Math.abs(Math.sin(s.walkPh)) * .03 * m;
    s.muzzleT -= dt; s.flash.visible = s.muzzleT > 0;
    s.flashT -= dt; s.torso.material.emissive.setHex(s.flashT > 0 ? 0x661111 : 0);
  }

  function updateCorpses(dt) {
    for (let i = corpses.length - 1; i >= 0; i--) {
      const c = corpses[i], s = c.s, g = s.group;
      c.t += dt;
      const k = Math.min(c.t / .8, 1), e = 1 - Math.pow(1 - k, 3);
      g.rotation.x = -e * (Math.PI / 2); g.rotation.z = c.roll * e;
      g.position.addScaledVector(c.slide, (1 - k) * 2.2 * dt);
      g.position.y = groundAt(g.position.x, g.position.z, g.position.y + .5) + .16 * e;
      s.legL.rotation.x = e * .35; s.legR.rotation.x = -e * .2;
      s.armL.rotation.x = -1.3 + e * 1.4; s.armR.rotation.x = -1.4 + e * 1.0;
      const r = Math.min(.2 + c.t * .35, 1.0); c.pool.scale.set(r, r, r);
      if (c.t > 14) g.position.y -= (c.t - 14) * dt;
      if (c.t > 17) { scene.remove(g); corpses.splice(i, 1); }
    }
  }

  // ====================== GAME FLOW ======================
  function startWave() {
    const onlineRoom = typeof mpRoom !== 'undefined' && mpRoom && mpRoom.status === 'playing';
    toSpawn = onlineRoom ? Number(mpRoom.bot_count || 0) : 5 + wave * 2;
    spawnT = 1; betweenWaves = toSpawn === 0;
    banner("Wave " + wave, 2);
    updateHUD();
  }
  function spawnEnemyAtEdge() {
    for (let tries = 0; tries < 12; tries++) {
      const side = Math.floor(Math.random() * 4), a = rnd(-ARENA + 4, ARENA - 4), e = ARENA - 4;
      const x = side < 2 ? (side ? e : -e) : a, z = side < 2 ? a : (side === 2 ? e : -e);
      if (Math.hypot(x - player.pos.x, z - player.pos.z) < 30) continue;
      const q = new V3(x, 0, z); collide(q, 0, .5);
      spawnSoldier("enemy", q.x, q.z); return;
    }
    spawnSoldier("enemy", ARENA - 4, ARENA - 4);
  }
  function spawnAlly() {
    const a = player.yaw + Math.PI;
    const q = new V3(player.pos.x + Math.sin(a) * 3, 0, player.pos.z + Math.cos(a) * 3); collide(q, player.pos.y, .5);
    ally = spawnSoldier("ally", q.x, q.z); updateHUD();
  }
  function clearDynamic() {
    soldiers.splice(0).forEach(s => scene.remove(s.group));
    corpses.splice(0).forEach(c => scene.remove(c.s.group));
    decals.splice(0).forEach(d => scene.remove(d));
    particles.splice(0).forEach(p => scene.remove(p.m));
    grenades.splice(0).forEach(g => scene.remove(g.m));
    smokes.splice(0).forEach(S => S.puffs.forEach(sp => scene.remove(sp)));
    fx.splice(0).forEach(f => scene.remove(f.m));
  }
  function resetGame() {
    clearDynamic();
    ally = null; allyRespawn = 0;
    Object.assign(player, { hp: 100, kills: 0, alive: true, vy: 0, onGround: true, reloadT: 0, fireCd: 0, scoped: false, yaw: 0, pitch: 0, camY: 1.7, nades: 3, smokes: 2, shake: 0, throwT: 0 });
    player.ws = W.map(w => ({ ammo: w.mag, reserve: w.reserve }));
    player.pos.set(0, 0, 0); wave = 1; msg.textContent = "";
    setWeapon(1); spawnAlly(); startWave();
  }
  function die() {
    player.alive = false; player.scoped = false; firing = false;
    document.exitPointerLock && document.exitPointerLock();
    showMenu("Click to play again");
    msg.textContent = "GAME OVER - Kills: " + player.kills + " | Wave: " + wave;
  }
  function showMenu(line) {
    menu.style.display = "flex"; $("menuLine").textContent = line; document.body.classList.remove("playing");
    firing = false; rightDown = false; Object.keys(keys).forEach(k => (keys[k] = false));
  }
  function pauseGame() {
    if (!gameRunning()) return;
    document.exitPointerLock && document.exitPointerLock();
    showMenu("Paused - click to continue");
  }

  // ====================== MENU / MAP SELECT ======================
  const mapsEl = $("maps");
  THEMES.forEach((t, i) => {
    const b = document.createElement("div"); b.className = "mapbtn" + (i === 0 ? " sel" : ""); b.textContent = t.name;
    b.addEventListener("click", e => { e.stopPropagation(); selMap = i; [...mapsEl.children].forEach((c, k) => c.classList.toggle("sel", k === i)); });
    mapsEl.appendChild(b);
  });
  function lockPointer() {
    try { const r = renderer.domElement.requestPointerLock(); if (r && r.catch) r.catch(() => {}); } catch (e) {}
  }
  menu.addEventListener("click", () => {
    if (!mpCanPlay) { mpShowPanel(); mpMe(); mpStatus('برای شروع، وارد حساب شوید یا «شروع بازی آفلاین» را بزنید.'); return; }
    if (audioCtx.state === "suspended") audioCtx.resume();
    if (selMap !== currentMap) { buildMap(selMap); resetGame(); }
    else if (!player.alive) resetGame();
    menu.style.display = "none"; document.body.classList.add("playing");
    if (touchMode) {
      try {
        const de = document.documentElement;
        const p = de.requestFullscreen ? de.requestFullscreen() : null;
        if (p && p.then) p.then(() => screen.orientation && screen.orientation.lock && screen.orientation.lock("landscape")).catch(() => {});
      } catch (e) {}
    } else lockPointer();
  });
  function enableTouch() {
    touchMode = true; document.body.classList.add("touch");
    document.querySelectorAll(".desk").forEach(e => (e.style.display = "none"));
    document.querySelectorAll(".mob").forEach(e => (e.style.display = "block"));
  }
  if (touchMode) enableTouch();
  addEventListener("touchstart", () => { if (!touchMode) enableTouch(); }, { passive: true });

  // ====================== DESKTOP INPUT ======================
  document.addEventListener("pointerlockchange", () => {
    locked = !!document.pointerLockElement;
    if (!locked && wasLocked && gameRunning() && !touchMode) pauseGame();
    wasLocked = locked;
  });
  addEventListener("keydown", e => {
    if (e.repeat && e.code !== "Space") return;
    keys[e.code] = true;
    if (!gameRunning()) return;
    switch (e.code) {
      case "KeyR": startReload(); break;
      case "Digit1": setWeapon(0); break; case "Digit2": setWeapon(1); break; case "Digit3": setWeapon(2); break;
      case "Digit4": setWeapon(3); break; case "Digit5": setWeapon(4); break;
      case "KeyG": throwGrenade("frag"); break;
      case "KeyH": throwGrenade("smoke"); break;
      case "KeyV": quickKnife(); break;
      case "Escape": pauseGame(); break;
      case "Space": e.preventDefault(); break;
    }
  });
  addEventListener("keyup", e => (keys[e.code] = false));
  addEventListener("wheel", e => { if (gameRunning()) setWeapon((player.wi + (e.deltaY > 0 ? 1 : NW - 1)) % NW); });
  addEventListener("contextmenu", e => e.preventDefault());
  addEventListener("mousemove", e => {
    mouseX = e.clientX; mouseY = e.clientY;
    if (!gameRunning() || touchMode) return;
    // free look: works with or without pointer lock, no button needs to be held
    const sens = .0022 * (camera.fov / 75);
    player.yaw -= (e.movementX || 0) * sens;
    player.pitch = clamp(player.pitch - (e.movementY || 0) * sens, -1.5, 1.5);
  });
  addEventListener("mousedown", e => {
    if (!gameRunning() || touchMode) return;
    if (e.target.closest && e.target.closest("#weapons")) return;
    if (e.button === 0) { firing = true; if (!W[player.wi].auto) tryFire(); }
    if (e.button === 2) rightDown = true;
  });
  addEventListener("mouseup", e => { if (e.button === 0) firing = false; if (e.button === 2) rightDown = false; });
  addEventListener("blur", () => { firing = false; rightDown = false; });
  addEventListener("resize", () => { camera.aspect = innerWidth / innerHeight; camera.updateProjectionMatrix(); renderer.setSize(innerWidth, innerHeight); });

  // ====================== TOUCH INPUT ======================
  function findTouch(list, id) { for (const t of list) if (t.identifier === id) return t; return null; }
  const joyBase = $("joybase"), joyKnob = $("joyknob");
  $("joyzone").addEventListener("touchstart", e => {
    e.preventDefault(); if (joy.id !== null) return;
    const t = e.changedTouches[0]; joy.id = t.identifier; joy.ox = t.clientX; joy.oy = t.clientY; joy.x = joy.y = 0;
    joyBase.style.display = joyKnob.style.display = "block";
    joyBase.style.left = joyKnob.style.left = t.clientX + "px"; joyBase.style.top = joyKnob.style.top = t.clientY + "px";
  }, { passive: false });
  $("joyzone").addEventListener("touchmove", e => {
    e.preventDefault(); const t = findTouch(e.changedTouches, joy.id); if (!t) return;
    let dx = (t.clientX - joy.ox) / 55, dy = (t.clientY - joy.oy) / 55; const l = Math.hypot(dx, dy);
    if (l > 1) { dx /= l; dy /= l; }
    joy.x = dx; joy.y = dy;
    joyKnob.style.left = joy.ox + dx * 55 + "px"; joyKnob.style.top = joy.oy + dy * 55 + "px";
  }, { passive: false });
  const joyEnd = e => { const t = findTouch(e.changedTouches, joy.id); if (!t) return; joy.id = null; joy.x = joy.y = 0; joyBase.style.display = joyKnob.style.display = "none"; };
  $("joyzone").addEventListener("touchend", joyEnd); $("joyzone").addEventListener("touchcancel", joyEnd);

  $("lookzone").addEventListener("touchstart", e => {
    e.preventDefault(); if (lookId !== null) return;
    const t = e.changedTouches[0]; lookId = t.identifier; lookX = t.clientX; lookY = t.clientY;
  }, { passive: false });
  $("lookzone").addEventListener("touchmove", e => {
    e.preventDefault(); const t = findTouch(e.changedTouches, lookId); if (!t || !gameRunning()) return;
    const sens = .0048 * (camera.fov / 75);
    player.yaw -= (t.clientX - lookX) * sens; player.pitch = clamp(player.pitch - (t.clientY - lookY) * sens, -1.5, 1.5);
    lookX = t.clientX; lookY = t.clientY;
  }, { passive: false });
  const lookEnd = e => { if (findTouch(e.changedTouches, lookId)) lookId = null; };
  $("lookzone").addEventListener("touchend", lookEnd); $("lookzone").addEventListener("touchcancel", lookEnd);

  function bindBtn(id, down, up) {
    const el = $(id);
    el.addEventListener("touchstart", e => { e.preventDefault(); e.stopPropagation(); if (gameRunning() || id === "tPause") down(el); }, { passive: false });
    const end = e => { e.preventDefault(); if (up) up(el); };
    el.addEventListener("touchend", end); el.addEventListener("touchcancel", end);
  }
  bindBtn("tFire", () => { firing = true; if (!W[player.wi].auto) tryFire(); }, () => (firing = false));
  bindBtn("tAim", el => { touchScope = !touchScope; el.classList.toggle("on", touchScope); });
  bindBtn("tReload", () => startReload());
  bindBtn("tJump", () => (keys.Space = true), () => (keys.Space = false));
  bindBtn("tFrag", () => throwGrenade("frag"));
  bindBtn("tSmoke", () => throwGrenade("smoke"));
  bindBtn("tKnife", () => quickKnife());
  bindBtn("tNext", () => setWeapon((player.wi + 1) % NW));
  bindBtn("tPause", () => pauseGame());

  // ====================== PLAYER ATTACKS ======================
  const ray = new THREE.Raycaster();
  function quickKnife() {
    if (!gameRunning()) return;
    if (player.wi !== 4) { player.prevWi = player.wi; setWeapon(4); player.fireCd = 0; player.knifeRevert = .6; }
    tryFire();
  }
  function knifeAttack() {
    player.fireCd = W[4].rate; player.swing = 1; boom(4500, .08, .08, 0);
    const dir = new V3(0, 0, -1).applyQuaternion(camera.quaternion).normalize();
    ray.set(camera.position, dir); ray.far = W[4].range;
    const targets = [];
    for (const s of soldiers) if (s.team === "enemy") for (const m of s.hit) targets.push(m);
    const hits = ray.intersectObjects(targets.concat(blockers), false);
    if (!hits.length) return;
    const h = hits[0], s = h.object.userData.soldier;
    if (s) {
      const fwd = new V3(Math.sin(s.group.rotation.y), 0, Math.cos(s.group.rotation.y));
      const back = fwd.dot(new V3(dir.x, 0, dir.z).normalize()) > .4; // attacker is behind the target
      hurtSoldier(s, back ? 200 : W[4].dmg * (h.object.userData.part === "head" ? 1.6 : 1), h.object.userData.part, h.point, dir, true);
    } else emit(h.point, 4, "spark", 2, null, .04, .25);
  }
  function tryFire() {
    if (!gameRunning()) return;
    const w = W[player.wi], st = player.ws[player.wi];
    if (player.reloadT > 0 || player.fireCd > 0 || player.throwT > 0) return;
    if (w.melee) { knifeAttack(); return; }
    if (st.ammo <= 0) { if (st.reserve > 0) startReload(); else click(300, .06); player.fireCd = .3; return; }
    st.ammo--; player.fireCd = w.rate; player.recoil = 1;
    boom(w.snd[0], w.snd[1], .22, w.snd[2]);
    vmFlash[player.wi].visible = true; muzzleLight.intensity = 3;
    const moving = keys.KeyW || keys.KeyA || keys.KeyS || keys.KeyD || Math.hypot(joy.x, joy.y) > .2;
    const scoped = player.scoped;
    let spread = w.spread;
    if (player.wi === 3) spread = scoped ? (moving ? .01 : 0) : .05;
    else if (scoped) spread *= .5;
    if (moving) spread += .008;
    const origin = camera.position.clone();
    const targets = [];
    for (const s of soldiers) if (s.team === "enemy") for (const m of s.hit) targets.push(m);
    const all = targets.concat(blockers);
    for (let p = 0; p < w.pellets; p++) {
      const dir = new V3((Math.random() - .5) * 2 * spread, (Math.random() - .5) * 2 * spread, -1).applyQuaternion(camera.quaternion).normalize();
      ray.set(origin, dir); ray.far = 200;
      const hits = ray.intersectObjects(all, false);
      if (!hits.length) continue;
      const h = hits[0], s = h.object.userData.soldier;
      if (s) {
        const part = h.object.userData.part;
        hurtSoldier(s, w.dmg * (part === "head" ? 2.5 : part === "limb" ? .7 : 1), part, h.point, dir, true);
      } else {
        emit(h.point, 5, "spark", 2.5, h.face ? h.face.normal.clone().transformDirection(h.object.matrixWorld) : null, .05, .3);
        emit(h.point, 3, "dust", 1.2, null, .1, .6);
      }
    }
    player.pitch += w.kick * .35;
    updateHUD();
    if (st.ammo === 0 && st.reserve > 0) setTimeout(() => { if (gameRunning()) startReload(); }, 250);
  }

  // ====================== MAIN UPDATE ======================
  const hurtEl = $("hurt"), scopeEl = $("scope"), bannerEl = $("banner"), hmEl = $("hitmarker");
  const clock = new THREE.Clock();
  let breathT = 0;

  function update(dt) {
    const w = W[player.wi];

    // --- edge turning for desktop when the pointer is not locked (cursor can't leave the screen then)
    if (!touchMode && !locked) {
      const ex = mouseX / innerWidth, ey = mouseY / innerHeight, rate = 1.8 * (camera.fov / 75);
      if (ex < .03) player.yaw += rate * dt; else if (ex > .97) player.yaw -= rate * dt;
      if (ey < .03) player.pitch = clamp(player.pitch + rate * .6 * dt, -1.5, 1.5); else if (ey > .97) player.pitch = clamp(player.pitch - rate * .6 * dt, -1.5, 1.5);
    }

    // --- movement
    player.scoped = (rightDown || touchScope) && player.reloadT <= 0 && !w.melee;
    const joyMag = Math.hypot(joy.x, joy.y);
    const sprint = (keys.ShiftLeft || joyMag > .95) && !player.scoped;
    const speed = (sprint ? 8.5 : 5.5) * (player.scoped && player.wi === 3 ? .35 : player.scoped ? .6 : 1);
    const fwd = new V3(-Math.sin(player.yaw), 0, -Math.cos(player.yaw)), right = new V3(Math.cos(player.yaw), 0, -Math.sin(player.yaw));
    const mv = new V3();
    if (keys.KeyW) mv.add(fwd); if (keys.KeyS) mv.sub(fwd); if (keys.KeyD) mv.add(right); if (keys.KeyA) mv.sub(right);
    if (mv.lengthSq() > 0) mv.normalize();
    if (joyMag > .12) { mv.addScaledVector(right, joy.x); mv.addScaledVector(fwd, -joy.y); if (mv.length() > 1) mv.normalize(); }
    const moving = mv.lengthSq() > 0.01;
    mv.multiplyScalar(speed * dt);
    player.pos.add(mv);
    collide(player.pos, player.pos.y, .4);
    if (keys.Space && player.onGround) { player.vy = 7.2; player.onGround = false; }
    player.vy = Math.max(player.vy - 22 * dt, -12);
    player.pos.y += player.vy * dt;
    const gr = groundAt(player.pos.x, player.pos.z, player.pos.y);
    if (player.pos.y <= gr && player.vy <= 0) { player.pos.y = gr; player.vy = 0; player.onGround = true; }
    else if (player.pos.y > gr + .05) player.onGround = false;

    // --- camera
    const eye = player.pos.y + 1.7;
    player.camY += (eye - player.camY) * Math.min(1, dt * 18);
    if (player.onGround && moving) player.bob += dt * (sprint ? 14 : 9);
    const bobY = player.onGround && moving ? Math.sin(player.bob) * .035 : 0;
    player.shake = Math.max(0, player.shake - dt * .25);
    const sk = player.shake;
    camera.position.set(player.pos.x + (Math.random() - .5) * sk, player.camY + bobY + (Math.random() - .5) * sk, player.pos.z + (Math.random() - .5) * sk);
    breathT += dt;
    const sway = player.scoped ? Math.sin(breathT * 1.3) * .0012 : 0;
    camera.rotation.y = player.yaw + sway;
    camera.rotation.x = player.pitch + player.recoil * .02 + (player.scoped ? Math.cos(breathT * 1.7) * .001 : 0);
    player.recoil = Math.max(0, player.recoil - dt * 9);
    const targetFov = player.scoped ? w.zoom : 75;
    camera.fov += (targetFov - camera.fov) * Math.min(1, dt * 14); camera.updateProjectionMatrix();
    const sniperScope = player.wi === 3 && camera.fov < 30;
    scopeEl.style.opacity = sniperScope ? 1 : 0;
    vmRoot.visible = !sniperScope;

    // --- viewmodel animation
    const rp = player.reloadT > 0 ? 1 - player.reloadT / w.reload : 0;
    player.swing = Math.max(0, player.swing - dt * 4);
    player.throwT = Math.max(0, player.throwT - dt);
    const throwDip = player.throwT > 0 ? Math.sin((1 - player.throwT / .55) * Math.PI) : 0;
    vmRoot.position.set(.22 - (player.scoped && !sniperScope ? .22 : 0), -.24 + bobY * .8 - Math.sin(rp * Math.PI) * .22 - throwDip * .35, -.45 + player.recoil * .07 - player.swing * .3);
    vmRoot.rotation.set(Math.sin(rp * Math.PI) * -.7 + player.recoil * .05 - player.swing * .9, 0, player.swing * .5);
    vmFlash.forEach(f => { if (f.visible && player.recoil < .6) f.visible = false; });
    muzzleLight.intensity = Math.max(0, muzzleLight.intensity - dt * 30);

    // --- weapon logic
    player.fireCd -= dt;
    if (player.knifeRevert > 0) { player.knifeRevert -= dt; if (player.knifeRevert <= 0 && player.wi === 4 && player.prevWi !== 4) setWeapon(player.prevWi); }
    if (firing && w.auto) tryFire();
    if (player.reloadT > 0) {
      player.reloadT -= dt;
      if (player.reloadT <= 0) {
        const st = player.ws[player.wi], take = Math.min(w.mag - st.ammo, st.reserve);
        st.ammo += take; st.reserve -= take; click(700, .08); updateHUD();
      }
    }

    // --- overlays
    if (player.hurtT > 0) player.hurtT -= dt;
    hurtEl.style.opacity = clamp(player.hurtT * 3, 0, 1);
    if (hmT > 0) { hmT -= dt; if (hmT <= 0) hmEl.style.opacity = 0; }
    if (bannerT > 0) { bannerT -= dt; if (bannerT <= 0) bannerEl.style.opacity = 0; }

    // --- world
    for (const s of soldiers.slice()) updateSoldier(s, dt);
    updateCorpses(dt); updateGrenades(dt); updateSmokes(dt);
    if (snowPts) {
      const a = snowPts.geometry.attributes.position;
      for (let i = 0; i < a.count; i++) {
        let y = a.getY(i) - dt * 3.2; if (y < 0) y += 25;
        a.setY(i, y); a.setX(i, a.getX(i) + Math.sin(breathT + i) * dt * .3);
      }
      a.needsUpdate = true;
      snowPts.position.set(Math.round(camera.position.x / 30) * 30, 0, Math.round(camera.position.z / 30) * 30);
    }

    if (!ally && allyRespawn > 0) { allyRespawn -= dt; if (allyRespawn <= 0) { spawnAlly(); banner("Teammate is back!", 1.2); } }

    // --- waves
    const enemyCount = soldiers.filter(s => s.team === "enemy").length;
    if (toSpawn > 0) {
      spawnT -= dt;
      if (spawnT <= 0 && enemyCount < 7 + Math.min(wave, 5)) { spawnEnemyAtEdge(); toSpawn--; spawnT = 1.4; }
    } else if (enemyCount === 0 && !betweenWaves) {
      betweenWaves = true; waveTimer = 5; banner("Wave " + wave + " cleared!", 2.5);
    }
    if (betweenWaves) {
      waveTimer -= dt;
      if (waveTimer <= 0) {
        wave++; player.hp = Math.min(100, player.hp + 30);
        player.ws.forEach((st, i) => (st.reserve = Math.min(W[i].reserve, st.reserve + W[i].mag * 2)));
        player.nades = Math.min(3, player.nades + 1); player.smokes = Math.min(2, player.smokes + 1);
        startWave();
      }
    }

    // --- particles & tracers
    for (let i = particles.length - 1; i >= 0; i--) {
      const p = particles[i];
      if (!p.rest) {
        p.v.y -= (p.kind === "smoke" ? 1 : 15) * dt; p.m.position.addScaledVector(p.v, dt);
        const gy = p.m.position.y < 8 ? groundAt(p.m.position.x, p.m.position.z, p.m.position.y) : 0;
        if (p.m.position.y <= gy + .02) {
          p.m.position.y = gy + .02; p.rest = true; p.v.set(0, 0, 0);
          if (p.kind === "blood" && Math.random() < .25) addDecal(p.m.position.x, p.m.position.z, gy, rnd(.1, .3));
        }
      }
      p.life -= dt;
      if (p.life <= 0) { scene.remove(p.m); particles.splice(i, 1); }
    }
    for (let i = tracers.length - 1; i >= 0; i--) {
      const t = tracers[i]; t.t -= dt;
      if (t.t <= 0) { scene.remove(t.l); t.l.geometry.dispose(); t.l.material.dispose(); tracers.splice(i, 1); }
    }
  }


  // ====================== MULTIPLAYER CLIENT (PHP JSON API) ======================
  const MP_API = 'api.php';
  let mpUser = null, mpRoom = null, mpPoll = null, mpLastMap = -1;
  const mpPeers = new Map();
  const mpEl = id => document.getElementById(id);
  const mpPanel = mpEl('mp-panel');
  const mpStatus = (text, kind='') => { const el=mpEl('mp-status'); el.textContent=text; el.className='mp-status '+kind; };
  async function mpApi(action, data={}) {
    const res = await fetch(MP_API, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({action,...data})});
    const json = await res.json().catch(()=>({ok:false,error:'پاسخ سرور قابل‌خواندن نیست.'}));
    if(!res.ok || !json.ok) throw new Error(json.error || 'درخواست انجام نشد.'); return json;
  }
  function mpShowPanel(){mpPanel.classList.add('show');}
  function mpHidePanel(){mpPanel.classList.remove('show');}
  function mpAuthView(){mpEl('mp-auth').hidden=!!mpUser;mpEl('mp-account').hidden=!mpUser;mpEl('mp-room').hidden=!mpRoom;mpEl('mp-welcome').textContent=mpUser?('سلام، '+mpUser.display_name+' (@'+mpUser.username+')'):'';}
  async function mpMe(){try{const r=await mpApi('me');mpUser=r.user;mpAuthView();if(mpUser)await mpLoadRooms();}catch(e){mpStatus(e.message,'error');}}
  async function mpLoadRooms(){if(!mpUser)return;try{const r=await mpApi('list_rooms');const root=mpEl('mp-rooms');root.innerHTML='';if(!r.rooms.length){root.textContent='اتاق عمومی فعالی پیدا نشد؛ می‌توانید اتاق بسازید یا با کد دعوت وارد اتاق خصوصی شوید.';}r.rooms.forEach(room=>{const row=document.createElement('div');row.className='mp-room-item';const label=document.createElement('span');label.textContent=`${room.name} · ${room.map_name} · ${room.count}/${room.capacity} · ${room.bot_count||0} بات · ${room.mode_label} · ${room.status==='playing'?'در حال بازی':'لابی'}`;const b=document.createElement('button');b.textContent=room.status==='playing'?'در حال بازی':'پیوستن';b.disabled=room.status!=='lobby'||(room.count+(room.bot_count||0))>=room.capacity;b.onclick=()=>mpJoin(room.code);row.append(label,b);root.append(row);});const ur=await mpApi('list_users');const users=mpEl('mp-users');users.innerHTML='';if(!ur.users.length){users.textContent='هنوز کاربری ثبت‌نام نکرده است.';}ur.users.forEach(u=>{const row=document.createElement('div');row.className='mp-member';const name=document.createElement('span');name.textContent=u.display_name+' (@'+u.username+')';const state=document.createElement('span');state.textContent=u.online?'● آنلاین':'○ آفلاین';state.style.color=u.online?'#8de6b2':'#8b96a8';row.append(name,state);users.append(row);});}catch(e){mpStatus(e.message,'error');}}
  async function mpJoin(code){try{const r=await mpApi('join_room',{code});mpRoom=r.room;mpRenderRoom();mpStatus('با موفقیت به اتاق پیوستید. کد را برای دوستان بفرستید.','ok');await mpLoadRooms();mpStartPolling();}catch(e){mpStatus(e.message,'error');}}
  function mpRenderRoom(){mpAuthView();if(!mpRoom)return;mpEl('mp-room-code').textContent=mpRoom.code;mpEl('mp-map').value=String(mpRoom.map);mpEl('mp-mode').value=mpRoom.mode;mpEl('mp-cap').value=String(mpRoom.capacity);mpEl('mp-bots').value=String(mpRoom.bot_count||0);mpEl('mp-private').checked=!!mpRoom.is_private;const list=mpEl('mp-members');list.innerHTML='';(mpRoom.members||[]).forEach(m=>{const row=document.createElement('div');row.className='mp-member';const name=document.createElement('span');name.textContent=m.display_name+(m.is_host?' · میزبان':'');const state=document.createElement('span');state.textContent=m.online?'● آنلاین':'○ آفلاین';state.style.color=m.online?'#8de6b2':'#8b96a8';row.append(name,state);list.append(row);});for(let i=1;i<=(mpRoom.bot_count||0);i++){const row=document.createElement('div');row.className='mp-member';row.innerHTML='<span>🤖 Bot '+i+'</span><span style="color:#ffbd66">بات</span>';list.append(row);}mpEl('mp-save').disabled=!mpRoom.is_host;mpEl('mp-start').disabled=!mpRoom.is_host;['mp-map','mp-mode','mp-cap','mp-bots','mp-private'].forEach(id=>{mpEl(id).disabled=!mpRoom.is_host;});}
  async function mpPollState(){if(!mpRoom)return;try{const r=await mpApi('room_state',{code:mpRoom.code,position:{x:player.pos.x,y:player.pos.y,z:player.pos.z,yaw:player.yaw,pitch:player.pitch,hp:player.hp,kills:player.kills,alive:player.alive}});mpRoom=r.room;mpRenderRoom();if(mpRoom.status==='playing'&&menu.style.display!=='none'){mpCanPlay=true;mpHidePanel();if(currentMap!==mpRoom.map){buildMap(mpRoom.map);selMap=mpRoom.map;}if(!player.alive)resetGame();menu.click();}if(mpRoom.map!==mpLastMap){mpLastMap=mpRoom.map;if(currentMap!==mpRoom.map){buildMap(mpRoom.map);player.pos.set(0,0,0);}}const live=new Set();(r.players||[]).forEach(p=>{if(p.id===mpUser.id)return;live.add(p.id);let obj=mpPeers.get(p.id);if(!obj){const group=new THREE.Group();const body=new THREE.Mesh(new THREE.BoxGeometry(.65,1.5,.45),new THREE.MeshStandardMaterial({color:p.color||0x39a8ff,roughness:.7}));body.position.y=.8;group.add(body);const head=new THREE.Mesh(new THREE.SphereGeometry(.24,10,8),new THREE.MeshStandardMaterial({color:0xe0b894}));head.position.y=1.65;group.add(head);scene.add(group);obj={group};mpPeers.set(p.id,obj);}obj.group.position.set(p.position.x||0,p.position.y||0,p.position.z||0);obj.group.rotation.y=p.position.yaw||0;});for(const [id,obj] of mpPeers){if(!live.has(id)){scene.remove(obj.group);mpPeers.delete(id);}}}catch(e){if(/اتاق|سرور/.test(e.message)){mpStopPolling();mpRoom=null;mpRenderRoom();}mpStatus(e.message,'error');}}
  function mpStartPolling(){mpStopPolling();mpLastMap=-1;mpPollState();mpPoll=setInterval(mpPollState,700);}
  function mpStopPolling(){if(mpPoll){clearInterval(mpPoll);mpPoll=null;}for(const [,o] of mpPeers)scene.remove(o.group);mpPeers.clear();}
  let mpCanPlay=false;
  const mpGoOffline=()=>{mpStopPolling();mpRoom=null;mpHidePanel();mpCanPlay=true;};
  mpEl('mp-offline').onclick=mpGoOffline;mpEl('mp-offline-auth').onclick=mpGoOffline;
  mpEl('mp-continue').onclick=()=>{if(!mpUser){mpStatus('برای ادامه وارد حساب شوید یا بازی آفلاین را انتخاب کنید.','error');return;}mpCanPlay=true;mpHidePanel();};
  mpEl('mp-open').onclick=()=>{mpShowPanel();mpMe();};mpEl('mp-close').onclick=mpHidePanel;mpPanel.addEventListener('click',e=>{if(e.target===mpPanel)mpHidePanel();});
  let mpAuthMode='login';document.querySelectorAll('[data-auth]').forEach(b=>b.onclick=()=>{mpAuthMode=b.dataset.auth;document.querySelectorAll('[data-auth]').forEach(x=>x.classList.toggle('active',x===b));mpEl('mp-display-wrap').hidden=mpAuthMode!=='register';mpEl('mp-auth-submit').textContent=mpAuthMode==='register'?'ساخت حساب':'ورود به حساب';mpEl('mp-pass').autocomplete=mpAuthMode==='register'?'new-password':'current-password';});
  mpEl('mp-auth-submit').onclick=async()=>{try{const username=mpEl('mp-user').value.trim(),password=mpEl('mp-pass').value,display_name=mpEl('mp-display').value.trim();const r=await mpApi(mpAuthMode,{username,password,display_name});mpUser=r.user;mpAuthView();mpStatus(mpAuthMode==='register'?'حساب ساخته شد. خوش آمدید!':'با موفقیت وارد شدید.','ok');await mpLoadRooms();}catch(e){mpStatus(e.message,'error');}};
  mpEl('mp-logout').onclick=async()=>{try{await mpApi('logout');mpStopPolling();mpRoom=null;mpUser=null;mpAuthView();mpStatus('از حساب خارج شدید.');}catch(e){mpStatus(e.message,'error');}};
  mpEl('mp-create').onclick=async()=>{try{const r=await mpApi('create_room',{map:Number(mpEl('mp-new-map').value||0),mode:mpEl('mp-new-mode').value||'coop',capacity:Number(mpEl('mp-new-cap').value||8),bot_count:Number(mpEl('mp-new-bots').value||0),is_private:mpEl('mp-new-private').checked});mpRoom=r.room;mpRenderRoom();mpStatus('سرور ساخته شد؛ '+(mpRoom.is_private?'خصوصی است و در فهرست عمومی دیده نمی‌شود. ':'عمومی است و برای همه کاربران نمایش داده می‌شود. ')+'کد دعوت: '+mpRoom.code,'ok');mpStartPolling();}catch(e){mpStatus(e.message,'error');}};
  mpEl('mp-join').onclick=()=>mpJoin(mpEl('mp-code').value.trim().toUpperCase());mpEl('mp-refresh').onclick=mpLoadRooms;
  mpEl('mp-save').onclick=async()=>{try{const r=await mpApi('update_room',{code:mpRoom.code,map:Number(mpEl('mp-map').value),mode:mpEl('mp-mode').value,capacity:Number(mpEl('mp-cap').value),bot_count:Number(mpEl('mp-bots').value),is_private:mpEl('mp-private').checked});mpRoom=r.room;mpRenderRoom();mpStatus('تنظیمات سرور ذخیره شد.','ok');await mpLoadRooms();}catch(e){mpStatus(e.message,'error');}};
  mpEl('mp-copy').onclick=async()=>{try{await navigator.clipboard.writeText(mpRoom.code);mpStatus('کد دعوت کپی شد: '+mpRoom.code,'ok');}catch(e){mpEl('mp-code').value=mpRoom.code;mpStatus('کد دعوت: '+mpRoom.code);}};
  mpEl('mp-leave').onclick=async()=>{try{await mpApi('leave_room',{code:mpRoom.code});mpStopPolling();mpRoom=null;mpRenderRoom();mpStatus('از اتاق خارج شدید.');await mpLoadRooms();}catch(e){mpStatus(e.message,'error');}};
  mpEl('mp-start').onclick=async()=>{try{const r=await mpApi('start_room',{code:mpRoom.code});mpRoom=r.room;mpCanPlay=true;mpHidePanel();if(currentMap!==mpRoom.map){buildMap(mpRoom.map);selMap=mpRoom.map;}if(!player.alive)resetGame();menu.click();}catch(e){mpStatus(e.message,'error');}};
  mpMe();
  mpShowPanel();
  setInterval(()=>{if(mpUser){mpLoadRooms();}},7000);

  const rotateEl = $("rotate");
  function loop() {
    requestAnimationFrame(loop);
    const dt = Math.min(clock.getDelta(), 1 / 30);
    const portrait = touchMode && innerHeight > innerWidth;
    rotateEl.style.display = portrait ? "flex" : "none";
    if (gameRunning() && !portrait) update(dt);
    renderer.render(scene, camera);
  }

  // ====================== BOOT ======================
  buildMap(0);
  camera.position.set(0, 1.7, 0);
  setWeapon(1);
  loop();
})();
</script>
</body>
</html>
