<?php
session_start();

// ⚠️ Reemplazá esto por tu Client ID real de Google Cloud Console
// (APIs y servicios > Credenciales > ID de cliente de OAuth 2.0 > tipo "Aplicación web")
$GOOGLE_CLIENT_ID = '286954706727-m7bmtukr3oq5fij9mf130oi82b47hbmr.apps.googleusercontent.com';

// El navegador manda acá el token que genera el botón de Google
if (isset($_POST['credential'])) {
    $jwt = $_POST['credential'];

    // Verificamos el token contra el endpoint oficial de Google (simple, sin librerías)
    $verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($jwt);
    $respuesta = @file_get_contents($verifyUrl);
    $datos = $respuesta ? json_decode($respuesta, true) : null;

    $tokenValido = $datos
        && isset($datos['aud'])
        && $datos['aud'] === $GOOGLE_CLIENT_ID
        && isset($datos['email']);

    if ($tokenValido) {
        $_SESSION['usuario'] = [
            'nombre'  => $datos['name']    ?? $datos['email'],
            'email'   => $datos['email'],
            'foto'    => $datos['picture'] ?? '',
            'verificado' => $datos['email_verified'] ?? false,
        ];
        header('Location: usuarios.php');
        exit;
    } else {
        $error = 'No pudimos verificar tu cuenta de Google. Probá de nuevo.';
    }
}

// Cerrar sesión
if (isset($_GET['salir'])) {
    session_destroy();
    header('Location: usuarios.php');
    exit;
}

$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuarios · FRT Enterprises</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<style>
  :root{
    --bg:#111319; --card:#1e222c; --text:#f4f2ea; --muted:#8b8d97;
    --lime:#d4ff4a; --coral:#ff6b4a; --border:rgba(244,242,234,0.1); --radius:14px;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  body{
    background:var(--bg); color:var(--text); font-family:'Inter',sans-serif;
    min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
  }
  h1,h2{ font-family:'Space Grotesk',sans-serif; }
  .eyebrow{ font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:.12em; text-transform:uppercase; color:var(--lime); }
  .card{
    background:var(--card); border:1px solid var(--border); border-radius:var(--radius);
    padding:40px; max-width:420px; width:100%; text-align:center;
  }
  .card h2{ font-size:26px; margin:10px 0 8px; }
  .card p.sub{ color:var(--muted); font-size:14px; margin-bottom:28px; }
  .g-btn-wrap{ display:flex; justify-content:center; margin-bottom:16px; }
  .error{ background:rgba(255,107,74,0.12); color:var(--coral); border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:20px; }
  .volver{ display:inline-block; margin-top:24px; color:var(--muted); font-size:13px; }
  .volver:hover{ color:var(--text); }

  .perfil{ display:flex; flex-direction:column; align-items:center; }
  .perfil img{ width:76px; height:76px; border-radius:50%; margin-bottom:16px; border:2px solid var(--lime); }
  .perfil .nombre{ font-size:19px; font-weight:600; }
  .perfil .email{ color:var(--muted); font-size:14px; margin-top:2px; margin-bottom:24px; }
  .badge{ font-family:'JetBrains Mono',monospace; font-size:11px; color:var(--lime); background:rgba(212,255,74,0.1); padding:4px 10px; border-radius:999px; margin-bottom:20px; }
  .btn-salir{
    font-family:'Inter',sans-serif; font-weight:600; font-size:14px; padding:12px 24px;
    border-radius:999px; border:1px solid var(--border); background:transparent; color:var(--text); cursor:pointer;
  }
  .btn-salir:hover{ background:rgba(255,107,74,0.1); border-color:var(--coral); color:var(--coral); }
</style>
</head>
<body>

<div class="card">
  <p class="eyebrow">FRT Enterprises</p>

  <?php if ($usuario): ?>

    <div class="perfil">
      <?php if ($usuario['foto']): ?>
        <img src="<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil">
      <?php endif; ?>
      <div class="nombre"><?= htmlspecialchars($usuario['nombre']) ?></div>
      <div class="email"><?= htmlspecialchars($usuario['email']) ?></div>
      <span class="badge">Sesión iniciada con Google</span>
      <a href="usuarios.php?salir=1"><button class="btn-salir">Cerrar sesión</button></a>
    </div>

  <?php else: ?>

    <h2>Usuarios</h2>
    <p class="sub">Iniciá sesión con tu cuenta de Google para entrar.</p>

    <?php if (isset($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="form-login" method="POST" style="display:none;">
      <input type="hidden" name="credential" id="credential-input">
    </form>

    <div id="g_id_onload"
      data-client_id="<?= htmlspecialchars($GOOGLE_CLIENT_ID) ?>"
      data-callback="handleCredentialResponse">
    </div>
    <div class="g-btn-wrap">
      <div class="g_id_signin"
        data-type="standard"
        data-theme="filled_black"
        data-size="large"
        data-text="signin_with"
        data-shape="pill">
      </div>
    </div>

  <?php endif; ?>

  <a href="frt-enterprises.html" class="volver">&larr; Volver al inicio</a>
</div>

<script>
  function handleCredentialResponse(response) {
    document.getElementById('credential-input').value = response.credential;
    document.getElementById('form-login').submit();
  }
</script>

</body>
</html>
