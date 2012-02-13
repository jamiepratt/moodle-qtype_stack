<?php
// This file is part of Stack - http://stack.bham.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for STACK_AnsTest_CompSquare.
 *
 * @copyright  2012 The University of Birmingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../anstest.class.php');
require_once(dirname(__FILE__) . '/../compsquare.class.php');


/**
 * Unit tests for STACK_AnsTest_CompSquare.
 *
 * @copyright  2012 The University of Birmingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class STACK_AnsTest_CompSquare_test extends UnitTestCase {

    public function test_is_true_for_completed_quadratics() {
        $at = new STACK_AnsTest_CompSquare('(x-1)^2-2', '(x-1)^2-2', null, 'x');
        $this->assertTrue($at->doAnsTest());
        $this->assertEqual(1, $at->getATMark());
    }

    public function test_is_false_for_wrong_form() {
        $at = new STACK_AnsTest_CompSquare('x^2+2*x+1', '(x+1)^2', null,'x');
        $this->assertFalse($at->doAnsTest());
        $this->assertEqual(0, $at->getATMark());
    }

    public function test_is_false_for_missing_option() {
        $at = new STACK_AnsTest_CompSquare('(x+1)^2', '(x+1)^2');
        $this->assertFalse($at->doAnsTest());
        $this->assertEqual(0, $at->getATMark());
    }
}
