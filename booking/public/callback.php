<?php

file_put_contents(__DIR__.'/callback.log',date('Y-m-d H:i:s').' '.print_r($_REQUEST, true), FILE_APPEND);
