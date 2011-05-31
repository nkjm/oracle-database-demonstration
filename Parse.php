<?php
class Parse {
    public $err_msg = array();

    public function id($input) {
        $pattern = '/^[\w_-]+$/';
        $trimmed_input = trim($input);
        if (!preg_match($pattern, $trimmed_input)) {
            array_push($this->err_msg, "許可されている文字列はアルファベット、数字、_(アンダースコア）,-(ハイフン)です。");
            return(FALSE);
        }
        return($trimmed_input);
    }

    public function password($input) {
        return(self::id($input));
    }
    
    public function select($input, $array_option) {
        $trimmed_input = trim($input);
        if (array_search($trimmed_input, $array_option) === FALSE) {
            array_push($this->err_msg, "不正な文字列が入力されました。");
            return(FALSE);
        }
        return($trimmed_input);
    }

    public function max_gbytes($input) {
        if (!is_numeric($input)) {
            array_push($this->err_msg, "許可されている文字列は数字です。");
            return(FALSE);
        }
        if ($input > 1000000) {
            array_push($this->err_msg, "許可されている数字は1000000までです。");
            return(FALSE);
        }
        return($input);
    }

    public function disk_path($input) {
        $pattern = '/^[\w/]+$/';
        $trimmed_input = trim($input);
        /*
        if (!preg_match($pattern, $trimmed_input)) {
            array_push($this->err_msg, "許可されている文字列はアルファベットです。");
            return(FALSE);
        }
        */
        return($trimmed_input);
    }

    public function fc_db_flash_cache_size($input) {
        if (!is_numeric($input)) {
            array_push($this->err_msg, "許可されている文字列は数字です。");
            return(FALSE);
        }
        if ($input > 25) {
            array_push($this->err_msg, "許可されている数字は25までです。");
            return(FALSE);
        }
        return($input);
    }

    public function timestamp($input) {
        $pattern = '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d$/';
        $trimmed_input = trim($input);
        if (!preg_match($pattern, $trimmed_input)) {
            array_push($this->err_msg, "Invalide String. String should be like '0000-00-00 00:00'.");
            return(FALSE);
        }
        $timestamp = $trimmed_input . ':00';
        return($timestamp);
    }
}
?>
