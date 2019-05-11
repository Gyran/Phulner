<?php
if (isset($_POST["newpassword"])) {
/*Phulner
{
    "identifier": "csrf",
    "action": "guard"
}
*/
    if ($_POST["csrf_token"] !== $_COOKIE["csrf_token"]) {
        die("csrf detected");
    }
/*/Phulner*/

    $newpassword = $_POST["newpassword"];
    $user = get_current_user();

    update_user_password($user, $_POST["newpassword"]);
}

/*Phulner
{
    "identifier": "csrf",
    "action": "generate"
}
*/
$csrf_token = base64_encode(openssl_random_pseudo_bytes(32));
setcookie("csrf_token", $csrf_token);
/*/Phulner*/

?>
<form method="post">
    New password: <input type="password" name="newpassword"><br>
<?php
/*Phulner
{
    "identifier": "csrf",
    "action": "include"
}
*/
echo "<input type='hidden' name='csrf_token'";
echo " value='" . $csrf_token. "'>";
/*/Phulner*/
?>
    <input type="submit" value="Change password">
</form>
