<?php

class PHPUnit_Framework_TestCase {

    private $assertOk=0;
    private $assertError=0;
    private $errorText="";

    public function assertStringStartsWith($prefix,$actual,$message='') {
        if ($prefix===substr($actual,0,strlen($prefix))) {
            $this->assertOk++;
        } else {
            $this->assertError++;
            if ($message=='') {
                $this->errorText .= 'Failed asserting that ' . $actual. '  starts with '.$prefix."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
        }
    }


    public function assertStringEndsWith($suffix,$actual,$message='') {
        if ($suffix===substr($actual,strlen($actual)-strlen($suffix))) {
            $this->assertOk++;
        } else {
            $this->assertError++;
            if ($message=='') {
                $this->errorText .= 'Failed asserting that ' . $actual. '  ends with '.$suffix."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
        }
    }

    public function assertSame($expected,$actual,$message='') {
        if ($expected===$actual) {
            $this->assertOk++;
        } else {
            $this->assertError++;
            if ($message=='') {
                $this->errorText .= 'Failed asserting that ' . $actual. ' is identical to '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
        }
    }

    public function assertNotSame($expected,$actual,$message='') {
        if ($expected!==$actual) {
            $this->assertOk++;
        } else {
            $this->assertError++;
            if ($message=='') {
                $this->errorText .= 'Failed asserting that ' . $actual. ' is different to '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
        }
    }

    public function assertTrue($condition,$message='') {
        if ($condition===true) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that false is true'."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertFalse($condition,$message='') {
        if ($condition===false) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that true is false'."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertNotNull($object,$message='') {
        if (null!==$object) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= "Failed asserting that '".$object."' is not null"."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertNull($object,$message='') {
        if (null===$object) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= "Failed asserting that '".$object."' is null"."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertGreaterThan($expected, $actual, string $message = '') {
        if ($actual > $expected) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that '.$actual.'  is greater then '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertGreaterThanOrEqual($expected, $actual, string $message = '') {
        if ($actual >= $expected) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that '.$actual.'  is greater then or equal '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertLessThan($expected, $actual, string $message = '') {
        if ($actual < $expected) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that '.$actual.'  is less then '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertLessThanOrEqual($expected, $actual, string $message = '') {
        if ($actual <= $expected) {
            $this->assertOk++;
        } else {
            if ($message=='') {
                $this->errorText .= 'Failed asserting that '.$actual.'  is less then or equal '.$expected."<br />";
            } else {
                $this->errorText .= $message."<br />";
            }
            $this->assertError++;
        }
    }

    public function assertCount($expectedCount, $haystack, string $message = '') {
        if (is_countable($haystack)) {
            if ($expectedCount == sizeof($haystack)) {
                $this->assertOk++;
            } else {
                if ($message == '') {
                    $this->errorText .= 'Failed asserting that actual size ' . sizeof($haystack) . '  matches expected size ' . $expectedCount . "<br />";
                } else {
                    $this->errorText .= $message . "<br />";
                }
                $this->assertError++;
            }
        } else {
            if ($message == '') {
                $this->errorText .= "Failed asserting that object is countable"."<br />";
            } else {
                $this->errorText .= $message . "<br />";
            }
            $this->assertError++;
        }
    }

    public function assertGetUnitTestResult() {
        $ret = new stdClass();
        $ret->testResult = $this->assertError==0;
        $ret->assertError = $this->assertError;
        $ret->assertOk = $this->assertOk;
        $ret->errorText = $this->errorText;
        return $ret;
    }


}

