<?php
require 'db.php'; ensure_session();
session_destroy();
js_redirect('index.html');
