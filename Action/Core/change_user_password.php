<?php

include_once "FDL/freedom_util.php";

/**
 * Change password for the current user
 *
 * @param Action $action
 * @throws Exception
 */
function change_user_password(Action & $action)
{

    $return = array(
        "success" => true,
        "error" => array(),
        "data" => array()
    );

    try {

        $usage = new ActionUsage($action);

        $oldPassword = $usage->addRequiredParameter("old_password", "old password value");
        $newPassword1 = $usage->addRequiredParameter("new_password_1", "new password");
        $newPassword2 = $usage->addRequiredParameter("new_password_2", "new password verif value");

        $usage->setStrictMode(false);
        $usage->verify(true);

        $user = new_Doc('', $action->user->fid);
        /* @var $user _IUSER */
        $err = $user->canEdit();
        if ($err) {
            throw new Exception(_("CHANGE_PASSWORD:You can't modify your account"));
        }

        if (!$action->user->checkpassword($oldPassword)) {
            throw new Exception(_("CHANGE_PASSWORD:The old password is not good"));
        }

        $err = $user->constraintPassword($newPassword1, $newPassword2, $action->user->login);
        if (isset($err["err"]) && !empty($err["err"])) {
            throw new Exception(sprintf(_("CHANGE_PASSWORD:The provided password is not good (%s)"), $err["err"]));
        }

        $err = $user->testForcePassword($newPassword1);
        if ($err) {
            throw new Exception(sprintf(_("CHANGE_PASSWORD:The provided password is not good (%s)"), $err));
        }

        $err = $user->setPassword($newPassword1);
        if ($err) {
            throw new Exception(sprintf(_("CHANGE_PASSWORD:Unable to change password (%s)"), $err));
        }

    } catch (Exception $e) {
        $return["success"] = false;
        $return["error"][] = $e->getMessage();
        unset($return["data"]);
    }

    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');

}