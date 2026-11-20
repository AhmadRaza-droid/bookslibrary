<?php
// Legacy endpoint disabled: password resets must use the expiring OTP flow.
header('Location: forgot_password.php');
exit();
?>