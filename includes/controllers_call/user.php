<?php

function controller_user($act, $d) {
    if ($act == 'edit_window') return AUsers::user_edit_window($d);
    if ($act == 'edit_update') return AUsers::user_edit_update($d);
    if ($act == 'delete') return AUsers::user_delete($d);
    return '';
}
