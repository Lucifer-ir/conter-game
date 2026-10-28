# Counter Arena 3D

A browser-based 3D first-person arena shooter prototype built with HTML,
CSS, JavaScript, Three.js, and a lightweight PHP API. It includes an
offline play option and a basic online lobby with accounts,
public/private rooms, and room settings.

> **Project status:** Prototype / work in progress. Online
> synchronization currently uses HTTP polling and is not a fully
> authoritative competitive multiplayer server.

## Features

-   **3D FPS gameplay** in the browser.
-   **Multiple maps:** Forest Outpost, Desert Town, Industrial Yard, and
    Snow Base.
-   **Combat HUD:** health, ammo, weapon selection, score, wave display,
    hit marker, and damage effects.
-   **Weapons and actions:** shooting, aiming/scope, reloading, jumping,
    sprinting, knife, frag grenade, and smoke grenade controls.
-   **Keyboard and mouse controls** on desktop.
-   **Touch controls** for mobile devices.
-   **Offline play** without creating an account.
-   **Basic online accounts:** registration, login, and logout.
-   **Online lobby:** public rooms, private rooms with invite codes,
    room membership, and approximate online status.
-   **Room configuration:** map, game mode (Co-op, Team, Free-for-all),
    capacity, and configured bot count.
-   **PHP JSON API** with server-side input validation and file-based
    JSON storage.

## Tech stack

-   HTML5 / CSS3
-   JavaScript
-   [Three.js](https://threejs.org/) loaded from a CDN
-   PHP 8.1+
-   JSON files for prototype account and room storage

## Requirements

-   PHP **8.1 or later**
-   PHP JSON support
-   HTTPS enabled on the hosting domain
-   A writable `data/` directory for the PHP process
-   Internet access in the browser to load Three.js from its CDN

## Installation

1.  Upload the contents of this project to a PHP-enabled web host.

2.  Keep the expected directory structure:

    ``` text
    public_html/
    ├── data/
    │   └── .htaccess
    └── conter/
        ├── .htaccess
        ├── api.php
        └── index.php
    ```

3.  Make sure PHP can write to the `data/` directory. Use appropriate
    ownership and permissions for your hosting environment; **do not set
    permissions to `777`**.

4.  Ensure the site is served over HTTPS.

5.  Open the game in your browser, for example:

    ``` text
    https://your-domain.example/conter/
    ```

6.  The API creates `data/users.json` and `data/rooms.json` on first
    use. Keep the `data/` directory protected from direct web access.

The included `.htaccess` files are intended to help prevent direct
access to stored data. If your server uses Nginx or another web server,
configure equivalent access restrictions.

## Controls

### Desktop

  Action           Control
  ---------------- -------------------------
  Move             `W`, `A`, `S`, `D`
  Look             Mouse
  Fire             Left mouse button
  Aim / scope      Right mouse button
  Reload           `R`
  Jump             `Space`
  Sprint           `Shift`
  Select weapons   `1`--`5` or mouse wheel
  Quick knife      `V`
  Frag grenade     `G`
  Smoke grenade    `H`
  Pause            `Esc`

### Mobile

Use the left touch area to move and the right touch area to look around.
On-screen buttons provide fire, aim, reload, jump, grenade, knife,
weapon-switch, and pause actions. A landscape orientation may provide a
better experience.

## Online play

1.  Open the network panel from the game menu.
2.  Register an account or sign in.
3.  Create a public or private room, or join an existing room.
4.  Share the invite code to let others join a private room.

Online features are implemented as a lightweight prototype. Room and
player state are exchanged through HTTP polling. This is suitable for
experimentation, but it does **not** provide authoritative server-side
validation for combat, damage, collision, or full bot AI. The configured
bot count is stored as room metadata; it should not be interpreted as a
complete server-driven bot system.

## Data and security notes

-   User records are stored in `data/users.json`; passwords are stored
    as hashes using PHP's `password_hash()` API.
-   Room state is stored in `data/rooms.json`.
-   These JSON files are created by the API and are intentionally not
    included in the deployment package.
-   Back up the `data/` directory before deploying changes that might
    affect stored data.
-   Keep `data/` inaccessible over HTTP. Verify the protection works on
    your host, especially if you use a server that does not honor Apache
    `.htaccess` rules.
-   Use HTTPS in production.
-   This prototype does not replace a production-ready authentication,
    rate-limiting, anti-cheat, or authoritative multiplayer
    architecture. Review and harden the API before exposing it to a
    large public audience.

## Known limitations

-   Multiplayer state uses HTTP polling rather than WebSockets or a
    dedicated real-time game server.
-   Combat outcomes and physics are not authoritatively validated by the
    server.
-   Bot count is a room setting; full online AI behavior is not
    implemented by the API.
-   Three.js is loaded from a CDN, so the game needs an internet
    connection to load that dependency.
-   Performance depends on the browser and device.

## Contributing

Issues and pull requests are welcome. For substantial changes, please
open an issue first to discuss the proposed approach. When reporting a
bug, include your browser, device, PHP version (for server/API issues),
steps to reproduce, and relevant console or server errors. Do not post
passwords, session cookies, or private user data.

## License

No license has been specified in this repository yet. Unless a license
is added, the default copyright rules apply and reuse, redistribution,
and modification are not automatically granted. Add a license file
before inviting others to reuse or redistribute the project.

------------------------------------------------------------------------

## راهنمای کوتاه فارسی

**Counter Arena 3D** یک نمونه اولیه بازی تیراندازی اول‌شخص سه‌بعدی در
مرورگر است. بازی حالت آفلاین دارد و بخش آنلاین آن شامل حساب کاربری، اتاق
عمومی و خصوصی، کد دعوت و تنظیمات اتاق است.

### نصب روی هاست

-   به PHP نسخه 8.1 یا بالاتر و HTTPS نیاز دارید.
-   فایل‌ها را با حفظ ساختار پوشه‌ها روی هاست قرار دهید.
-   پوشه `data/` باید برای PHP قابل‌نوشتن و از دسترسی مستقیم وب محافظت
    شده باشد؛ مجوز `777` ندهید.
-   فایل‌های `data/users.json` و `data/rooms.json` هنگام اولین درخواست
    API ساخته می‌شوند.
-   برای اجرای بازی، مسیر `/conter/` را در دامنه خود باز کنید.

### نکته درباره بخش آنلاین

بخش چندنفره فعلی نمونه اولیه است و با درخواست‌های HTTP دوره‌ای کار می‌کند.
این نسخه سرور رقابتی کامل با اعتبارسنجی سمت سرور برای تیراندازی، آسیب،
برخوردها و هوش مصنوعی کامل بات‌ها نیست. قبل از انتشار عمومی گسترده، امنیت
API، محدودسازی درخواست‌ها و معماری سرور باید بازبینی و تقویت شود.
