<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function flash_set(string $type, string $msg): void {
  $_SESSION['__flash'][] = ['type'=>$type, 'msg'=>$msg];
}
function flash_show(): void {
  if (empty($_SESSION['__flash'])) return;
  foreach ($_SESSION['__flash'] as $f) {
    $cls = $f['type']==='error' ? 'flash-error' : 'flash-ok';
    echo '<div class="'.$cls.'">'.htmlspecialchars($f['msg']).'</div>';
  }
  unset($_SESSION['__flash']);
}
