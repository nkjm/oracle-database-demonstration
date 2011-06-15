<?php
class Error {
    public $error = 0;
    public $stack_msg = array();
    public $stack_log = array();
    public $skip = FALSE;

    public function set_msg($msg) {
        $this->error = 1;
        array_push($this->stack_msg, $msg);
    }

    public function set_log($log) {
        array_push($this->stack_log, $log);
    }

    public function check($exit = TRUE, $output = 'json') {
        if ($this->error == 1) {
            if ($output == 'json') {
                print json_encode($this);
            } elseif ($output == 'html') {
                print "<div class='flag_error' style='display:none;'>TRUE</div>\n";
                foreach ($this->stack_msg as $msg) {
                    print "<div class='error_msg' style='display:none;'>" . $msg . "</div>\n";
                }
            }
            if ($exit == TRUE) {
                exit();
            }
        }
    }

    public function flush() {
        print json_encode($this);
    }
}
?>
