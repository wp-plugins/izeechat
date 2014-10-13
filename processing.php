<?php
$config = include MYPLUGIN_ROOT.DS.'config.php';
$process = new IzeeProcess($config['api']['host']);

if (!empty($_POST)) {
	if (isset($_POST['register_submit']))
		$process->registerProcess($_POST);

	if (isset($_POST['authentication_submit']))
		$process->authenticationProcess($_POST);

	if (isset($_POST['login_submit']))
		$process->loginProcess($_POST);

	if (isset($_POST['registration_submit']))
		$process->registrationProcess($_POST);

	if (isset($_POST['create_submit']))
		$process->createProcess($_POST);

	if (isset($_POST['recup_submit']))
		$process->getSiteKeyProcess($_POST);
}
?>