<?php

class AUsers {

    public static function user_info($user_id) {
        $q = DB::query("SELECT *
            FROM users WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
        if ($row = DB::fetch_row($q)) {
            return [
                'id' => (int) $row['user_id'],
                'village_id' => $row['village_id'],
                'plot_id' => $row['plot_id'],
                'access' => $row['access'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone' => $row['phone']
            ];
        } else {
            return [
                'id' => 0,
                'village_id' => 0,
                'plot_id' => '',
                'access' => '',
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => ''
            ];
        }
    }

    public static function users_list($d = []) {
        // vars
        $search = isset($d['search']) && trim($d['search']) ? $d['search'] : '';
        $offset = isset($d['offset']) && is_numeric($d['offset']) ? $d['offset'] : 0;
        $limit = 20;
        $items = [];
        // where
        $where = [];
        if ($search) $where[] = "(first_name LIKE '%".$search."%' OR email LIKE '%".$search."%' OR phone LIKE '%".$search."%')";
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        // info
        $q = DB::query("SELECT user_id, plot_id, first_name, last_name, phone, email, last_login
            FROM users ".$where." ORDER BY user_id+0 LIMIT ".$offset.", ".$limit.";") or die (DB::error());
        while ($row = DB::fetch_row($q)) {
            $items[] = [
                'id' => (int) $row['user_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'plot_id' => $row['plot_id'],
                'last_login' => date('Y/m/d', $row['last_login'])
            ];
        }
        // paginator
        $q = DB::query("SELECT count(*) FROM users ".$where.";");
        $count = ($row = DB::fetch_row($q)) ? $row['count(*)'] : 0;
        $url = 'users';
        if ($search) $url .= '?search='.$search.'&';
        paginator($count, $offset, $limit, $url, $paginator);
        // output
        return ['items' => $items, 'paginator' => $paginator];
    }

    public static function users_fetch($d = []) {
        $info = AUsers::users_list($d);
        HTML::assign('users', $info['items']);
        return ['html' => HTML::fetch('./partials/users_table.html'), 'paginator' => $info['paginator']];
    }

    public static function user_edit_window($d = []) {
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        HTML::assign('user', AUsers::user_info($user_id));
        return ['html' => HTML::fetch('./partials/user_edit.html')];
    }

    public static function user_edit_update($d = []) {
        // vars
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $first_name = isset($d['first_name']) && trim($d['first_name']) ? trim($d['first_name']) : '';
        $last_name = isset($d['last_name']) && trim($d['last_name']) ? trim($d['last_name']) : '';
        $phone = isset($d['phone']) && trim($d['phone']) ? preg_replace('~\D+~', '', trim($d['phone'])) : '';
        $email = isset($d['email']) && trim($d['email']) ? strtolower(trim($d['email'])) : '';
        $plot_id = isset($d['plot_id']) ? $d['plot_id'] : '';
        if (is_array($plot_id)) {
            $plot_id = implode(',', $plot_id);
        }
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;

        if (empty($first_name) || empty($last_name) || empty($phone) || empty($email)) {
            return false;
        }

        if ($user_id) {
            $set = [];
            $set[] = "first_name='".$first_name."'";
            $set[] = "last_name='".$last_name."'";
            $set[] = "phone='".$phone."'";
            $set[] = "email='".$email."'";
            $set[] = "plot_id='".$plot_id."'";
            $set[] = "updated='".Session::$ts."'";
            $set = implode(", ", $set);
            DB::query("UPDATE users SET ".$set." WHERE user_id='".$user_id."' LIMIT 1;") or die (DB::error());
        } else {
            DB::query("INSERT INTO users (
                first_name,
                last_name,
                phone,
                email,
                plot_id,
                updated
            ) VALUES (
                '".$first_name."',
                '".$last_name."',
                '".$phone."',
                '".$email."',
                '".$plot_id."',
                '".Session::$ts."'
            );") or die (DB::error());
        }
        // output
        return AUsers::users_fetch(['offset' => $offset]);
    }

    public static function user_delete($d = []) {
        $user_id = isset($d['user_id']) && is_numeric($d['user_id']) ? $d['user_id'] : 0;
        $offset = isset($d['offset']) ? preg_replace('~\D+~', '', $d['offset']) : 0;
        
        DB::query("DELETE FROM users WHERE user_id = '".$user_id."' LIMIT 1;") or die(DB::error());

        return AUsers::users_fetch(['offset' => $offset]);
    }

}
