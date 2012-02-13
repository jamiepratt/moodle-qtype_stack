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
 * The file provides helper code for creating the files needed to connect to the CAS.
 */

require_once(dirname(__FILE__).'/../../../../../config.php');

require_once(dirname(__FILE__) . '/../../locallib.php');
require_once(dirname(__FILE__) . '/../stringutil.class.php');


class stack_cas_configuration {

    /** @var string the date when these settings were worked out. */
    protected $date;

    /**
     * Constructor, initialises all the settings.
     */
    public function __construct() {
        global $CFG;

        $settings = get_config('qtype_stack');
        $this->date = date("F j, Y, g:i a");

        $maximacodepath = new STACK_StringUtil($CFG->dirroot . '/question/type/stack/stack/maxima');
        $this->maximacodepath = $maximacodepath->convertSlashPaths();

        $logpath = new STACK_StringUtil($CFG->dataroot . '/stack');
        $this->logpath = $logpath->convertSlashPaths();

        $vnum = substr($settings->maximaversion, 2);
        $this->blocksettings = array();
        $this->blocksettings['MAXIMA_VERSION_NUM'] = $vnum;
        $this->blocksettings['MAXIMA_VERSION'] = $settings->maximaversion;
        $this->blocksettings['TMP_IMAGE_DIR'] = $CFG->dataroot . '/stack/tmp/';
        $this->blocksettings['IMAGE_DIR']     = $CFG->dataroot . '/stack/plots/';
        $this->blocksettings['URL_BASE']      = moodle_url::make_file_url('/question/type/stack/plot.php', '');

        // These are used by the GNUplot "set terminal" command.  Currently no user interface...
        $this->blocksettings['PLOT_TERMINAL'] = 'png';
        $this->blocksettings['PLOT_TERM_OPT'] = 'large transparent size 450,300';

        if ($settings->platform == 'win') {
            $this->blocksettings['DEL_CMD']       = 'del';
            $this->blocksettings['GNUPLOT_CMD']   =
                    $this->get_plotcommand_win($vnum, $settings->maximaversion);
        } else {
            $this->blocksettings['DEL_CMD']       = "rm";
            $this->blocksettings['GNUPLOT_CMD' ]  = $settings->plotcommand;
        }
    }

    /**
     * Try to guess the gnuplot command on Windows.
     * @return string the command.
     */
    public function get_plotcommand_win($vnum, $maximaversion) {
        if ($settings->plotcommand && $settings->plotcommand != 'gnuplot') {
            return $settings->plotcommand;
        }

        // This does its best to find your version of Gnuplot...
        if ($vnum > 25) {
            return '"c:/Program Files/Maxima-' . $maximaversion . '-gcl/gnuplot/wgnuplot.exe"';
        } else if ($vnum > 23) {
            return '"c:/Program Files/Maxima-' . $maximaversion . '/gnuplot/wgnuplot.exe"';
        } else {
            return '"c:/Program Files/Maxima-' . $maximaversion . '/bin/wgnuplot.exe"';
        }
    }

    public function get_maximalocal_contents() {
        $contents = <<<END
/* ***********************************************************************/
/* This file is automatically generated at installation time.            */
/* The purpose is to transfer configuration settings to Maxima.          */
/* Hence, you should not edit this file.  Edit your configuration.       */
/* This file is regularly overwritten, so your changes will be lost.     */
/* ***********************************************************************/

/* File generated on {$this->date} */

/* Add the location to Maxima's search path */
file_search_maxima:append( [sconcat("{$this->maximacodepath}/###.{mac,mc}")] , file_search_maxima)$
file_search_lisp:append( [sconcat("{$this->maximacodepath}/###.{lisp}")] , file_search_lisp)$
file_search_maxima:append( [sconcat("{$this->logpath}/###.{mac,mc}")] , file_search_maxima)$
file_search_lisp:append( [sconcat("{$this->logpath}/###.{lisp}")] , file_search_lisp)$

STACK_SETUP(ex):=block(

END;
        foreach ($this->blocksettings as $name => $value) {
            $contents .= <<<END
    {$name}:"{$value}",

END;
        }
        $contents .= <<<END
    true)$

/* Load the main libraries */
load("stackmaxima.mac")$

END;

        return $contents;
    }

    /**
     * Check whether the maximalocal.mac file exists, and if not, create it.
     */
    public static function verify_maximalocal_exists() {
        if (!is_readable(self::maximalocal_location())) {
            self::create_maximalocal();
        }
    }

    /**
     * Get the full path for the maximalocal.mac file.
     * @return string the full path to where the maximalocal.mac file should be stored.
     */
    public static function maximalocal_location() {
        global $CFG;
        return $CFG->dataroot . '/stack/maximalocal.mac';
    }

    /**
     * Create the maximalocal.mac file, overwriting it if it already exists.
     */
    public static function create_maximalocal() {
        make_upload_directory('stack');
        make_upload_directory('stack/tmp');
        make_upload_directory('stack/plots');
        file_put_contents(self::maximalocal_location(),
                self::generate_maximalocal_contents());
    }

    /**
     * Generate the contents for the maximalocal configuration file.
     * @return string the contets that the maximalocal.mac file should have.
     */
    public static function generate_maximalocal_contents() {
        $settings = new self();
        return $settings->get_maximalocal_contents();
    }
}
