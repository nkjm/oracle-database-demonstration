<?php
class Parse {
    public function id($input) {
        global $error;
        $pattern = '/^[\w_-]+$/';
        $trimmed_input = trim($input);
        if (!preg_match($pattern, $trimmed_input)) {
            $error->set_msg("Invalid string. Available letters are [a-zA-Z0-9_-].");
            return(ERROR);
        }
        return(strtoupper($trimmed_input));
    }

    public function password($input) {
        global $error;
        return(self::id($input));
    }
    
    public function select($input, $array_option) {
        global $error;
        $trimmed_input = trim($input);
        if (array_search($trimmed_input, $array_option) === FALSE) {
            $error->set_msg("illegal input detected.");
            return(ERROR);
        }
        return($trimmed_input);
    }

    public function max_gbytes($input) {
        global $error;
        if (!is_numeric($input)) {
            $error->set_msg("Invalid input. Available letters are numbers.");
            return;
        }
        if ($input > 1000000) {
            $error->set_msg("Invalid input. Available value is less than or equal to 10000000.");
            return(ERROR);
        }
        return($input);
    }

    public function disk_path($input) {
        global $error;
        $trimmed_input = trim($input);
        if ($trimmed_input == 'aws_new') {
            return($trimmed_input);
        }
        $pattern = '/^[\w-:\/]+$/';
        if (!preg_match($pattern, $trimmed_input)) {
            $error->set_msg("Invalid disk path. Available letters are [a-z0-9-:/]");
            return(ERROR);
        }
        return($trimmed_input);
    }

    public function fc_db_flash_cache_size($input) {
        global $error;
        if (!is_numeric($input)) {
            $error->set_msg("Invalid input. Available letters are numbers.");
            return(ERROR);
        }
        if ($input > 25) {
            $error->set_msg("Invalid input. Available value is less than or equal to 25.");
            return(ERROR);
        }
        return($input);
    }

    public function timestamp($input) {
        global $error;
        $pattern = '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d$/';
        $trimmed_input = trim($input);
        if (!preg_match($pattern, $trimmed_input)) {
            $error->set_msg("Invalid input. String should be like '0000-00-00 00:00'.");
            return(ERROR);
        }
        $timestamp = $trimmed_input . ':00';
        return($timestamp);
    }

/*
    public function aws_volume_id($input) {
        global $error;
        if ($trimmed_input == 'n/a') {
            return($trimmed_input);
        }
        $pattern = '/^[\w-]+$/';
        $trimmed_input = trim($input);
        if ($trimmed_input == 'n/a') {
            return($trimmed_input);
        }
        if (!preg_match($pattern, $trimmed_input)) {
            $error->set_msg("Invalid string. Available letters are [a-zA-Z0-9_-].");
            return(ERROR);
        }
        return($trimmed_input);
    }
*/
}
?>
