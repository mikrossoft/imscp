<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
 * @copyright 	2010 by i-msCP | http://i-mscp.net
 * @version 	SVN: $Id$
 * @link 		http://i-mscp.net
 * @author 		ispCP Team
 * @author 		i-MSCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 * Portions created by the i-MSCP Team are Copyright (C) 2010 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 */

require 'imscp-lib.php';

iMSCP_Events_Manager::getInstance()->dispatch(iMSCP_Events::onResellerScriptStart);

/************************************************************************************
 * Script functions
 */

/**
 * Generate page.
 *
 * @param  iMSCP_pTemplate $tpl Template engine
 * @param  int $reseller_id Reseller unique identifier
 * @param  string $reseller_name Reseller name
 * @return void
 */
function generate_page($tpl, $reseller_id, $reseller_name)
{

    /** @var $cfg iMSCP_Config_Handler_File */
    $cfg = iMSCP_Registry::get('config');

    $rows_per_page = (int)($cfg->DOMAIN_ROWS_PER_PAGE / 2);

    if (isset($_GET['psi'])) {
        $start_index = (int)trim($_GET['psi']);
    } else if (isset($_POST['psi'])) {
        $start_index = (int)trim($_POST['psi']);
    } else {
        $start_index = 0;
    }

    if (!is_numeric($start_index)) {
        $start_index = 0;
    }

    $tpl->assign(array('POST_PREV_PSI' => $start_index));
    // count query
    $count_query = "
		SELECT
			COUNT(`admin_id`) AS cnt
		FROM
			`admin`
		WHERE
			`admin_type` = 'user'
		AND
			`created_by` = ?
	";

    $rs = exec_query($count_query, $reseller_id);
    $records_count = $rs->fields['cnt'];

    $query = "
		SELECT
			`admin_id`
		FROM
			`admin`
		WHERE
			`admin_type` = 'user'
		AND
			`created_by` = ?
		ORDER BY
			`admin_name` ASC
		LIMIT
			$start_index, $rows_per_page
	";

    $rs = exec_query($query, $reseller_id);
    $tpl->assign(array(
                      'RESELLER_NAME' => tohtml($reseller_name),
                      'RESELLER_ID' => $reseller_id));

    if ($rs->rowCount() == 0) {
        $tpl->assign('PROPS_LIST' , '');
        set_page_message(tr('This reseller has no domains yet.'), 'info');
    } else {
        $prev_si = $start_index - $rows_per_page;
        if ($start_index == 0) {
            $tpl->assign('SCROLL_PREV', '');
        } else {
            $tpl->assign(array(
                              'SCROLL_PREV_GRAY' => '',
                              'PREV_PSI' => $prev_si));
        }

        $next_si = $start_index + $rows_per_page;

        if ($next_si + 1 > $records_count) {
            $tpl->assign('SCROLL_NEXT', '');
        } else {
            $tpl->assign(array(
                              'SCROLL_NEXT_GRAY' => '',
                              'NEXT_PSI' => $next_si));
        }
        $row = 1;

        while (!$rs->EOF) {
            $admin_id = $rs->fields['admin_id'];
            $query = "
				SELECT
					`domain_id`
				FROM
					`domain`
				WHERE
					`domain_admin_id` = ?
			";

            $dres = exec_query($query, $admin_id);
            generate_domain_entry($tpl, $dres->fields['domain_id'], $row++);
            $tpl->parse('DOMAIN_ENTRY', '.domain_entry');
            $rs->moveNext();
        }
    }
}

/**
 * Generates domain entry
 * 
 * @param  iMSCP_pTemplate $tpl Template engine
 * @param  $user_id
 * @param  $row
 * @return void
 */
function generate_domain_entry($tpl, $user_id, $row)
{
    global $crnt_month, $crnt_year;

    list($domain_name, $domain_id, $web, $ftp, $smtp, $pop3, $utraff_current,
        $udisk_current
    ) = generate_user_traffic($user_id);

    list($usub_current, $usub_max, $uals_current, $uals_max, $umail_current,
        $umail_max, $uftp_current, $uftp_max, $usql_db_current, $usql_db_max,
        $usql_user_current, $usql_user_max, $utraff_max, $udisk_max
    ) = generate_user_props($user_id);

    $utraff_max = $utraff_max * 1024 * 1024;
    $udisk_max = $udisk_max * 1024 * 1024;
    list($traff_percent, $traff_red, $traff_green) = make_usage_vals($utraff_current, $utraff_max);
    list($disk_percent, $disk_red, $disk_green) = make_usage_vals($udisk_current, $udisk_max);
    $traff_show_percent = $traff_percent;
    $disk_show_percent = $disk_percent;

    if ($traff_percent > 100) {
        $traff_percent = 100;
    }

    if ($disk_percent > 100) {
        $disk_percent = 100;
    }

    $tpl->assign(array('ITEM_CLASS' => ($row % 2 == 0) ? 'content' : 'content2'));

    $domain_name = decode_idna($domain_name);

    $tpl->assign(
        array(
             'DOMAIN_NAME' => tohtml($domain_name),
             'MONTH' => $crnt_month,
             'YEAR' => $crnt_year,
             'DOMAIN_ID' => $domain_id,

             'TRAFF_SHOW_PERCENT' => $traff_show_percent,
             'TRAFF_PERCENT' => $traff_percent,
             'TRAFF_RED' => $traff_red,
             'TRAFF_GREEN' => $traff_green,

             'TRAFF_MSG' => ($utraff_max)
                 ? tr('%1$s <br/>of<br/> <b>%2$s</b>', sizeit($utraff_current), sizeit($utraff_max))
                 : tr('%s <br/>of<br/> <b>unlimited</b>', sizeit($utraff_current)),

             'DISK_SHOW_PERCENT' => $disk_show_percent,
             'DISK_PERCENT' => $disk_percent,
             'DISK_RED' => $disk_red,
             'DISK_GREEN' => $disk_green,

             'DISK_MSG' => ($udisk_max)
                 ? tr('%1$s <br/>of<br/> <b>%2$s</b>', sizeit($udisk_current), sizeit($udisk_max))
                 : tr('%s <br/>of<br/> <b>unlimited</b>', sizeit($udisk_current)),

             'WEB' => sizeit($web),
             'FTP' => sizeit($ftp),
             'SMTP' => sizeit($smtp),
             'POP3' => sizeit($pop3),

             'SUB_MSG' => ($usub_max)
                 ? (($usub_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', sizeit($usub_current), $usub_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', sizeit($usub_current)),

             'ALS_MSG' => ($uals_max)
                 ? (($uals_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', sizeit($uals_current), $uals_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', sizeit($uals_current)),

             'MAIL_MSG' => ($umail_max)
                 ? (($umail_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', $umail_current, $umail_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', $umail_current),

             'FTP_MSG' => ($uftp_max)
                 ? (($uftp_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', $uftp_current, $uftp_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', $uftp_current),


             'SQL_DB_MSG' => ($usql_db_max)
                 ? (($usql_db_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', $usql_db_current, $usql_db_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', $usql_db_current),

             'SQL_USER_MSG' => ($usql_user_max)
                 ? (($usql_user_max > 0)
                     ? tr('%1$d <br/>of<br/> <b>%2$d</b>', $usql_user_current, $usql_user_max)
                     : tr('<b>disabled</b>'))
                 : tr('%d <br/>of<br/> <b>unlimited</b>', $usql_user_current)
        )
    );
}

/************************************************************************************
 * Main script
 */

check_login(__FILE__);

/** @var $cfg iMSCP_Config_Handler_File */
$cfg = iMSCP_Registry::get('config');

$tpl = new iMSCP_pTemplate();
$tpl->define_dynamic('page', $cfg->RESELLER_TEMPLATE_PATH . '/reseller_user_statistics.tpl');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('page_message', 'page');
$tpl->define_dynamic('logged_from', 'page');
$tpl->define_dynamic('month_list', 'page');
$tpl->define_dynamic('year_list', 'page');
$tpl->define_dynamic('domain_list', 'page');
$tpl->define_dynamic('domain_entry', 'domain_list');
$tpl->define_dynamic('scroll_prev_gray', 'page');
$tpl->define_dynamic('scroll_prev', 'page');
$tpl->define_dynamic('scroll_next_gray', 'page');
$tpl->define_dynamic('scroll_next', 'page');

$rid = $_SESSION['user_id'];
$name = $_SESSION['user_logged'];

if (isset($_POST['month']) && isset($_POST['year'])) {
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);
} else if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = intval($_GET['month']);
    $year = intval($_GET['year']);
} else {
    $month = date('m');
    $year = date('Y');
}

if (!is_numeric($rid) || !is_numeric($month) || !is_numeric($year)) {
    redirectTo('./reseller_statistics.php');
}

$tpl->assign(
    array(
         'TR_PAGE_TITLE' => tr('i-MSCP - Admin/Reseller User Statistics'),
         'THEME_COLOR_PATH' => "../themes/{$cfg->USER_INITIAL_THEME}",
         'THEME_CHARSET' => tr('encoding'),
         'ISP_LOGO' => layout_getUserLogo()));

gen_reseller_mainmenu($tpl, $cfg->RESELLER_TEMPLATE_PATH . '/main_menu_statistics.tpl');
gen_reseller_menu($tpl, $cfg->RESELLER_TEMPLATE_PATH . '/menu_statistics.tpl');
gen_logged_from($tpl);

$tpl->assign(
    array(
         'TR_RESELLER_USER_STATISTICS' => tr('Reseller users table'),
         'TR_MONTH' => tr('Month'),
         'TR_YEAR' => tr('Year'),
         'TR_SHOW' => tr('Show'),
         'TR_DOMAIN_NAME' => tr('Domain'),
         'TR_TRAFF' => tr('Traffic<br>usage'),
         'TR_DISK' => tr('Disk<br>usage'),
         'TR_WEB' => tr('Web<br>traffic'),
         'TR_FTP_TRAFF' => tr('FTP<br>traffic'),
         'TR_SMTP' => tr('SMTP<br>traffic'),
         'TR_POP3' => tr('POP3/IMAP<br>traffic'),
         'TR_SUBDOMAIN' => tr('Subdomain'),
         'TR_ALIAS' => tr('Alias'),
         'TR_MAIL' => tr('Mail'),
         'TR_FTP' => tr('FTP'),
         'TR_SQL_DB' => tr('SQL<br>database'),
         'TR_SQL_USER' => tr('SQL<br>user'),
         'VALUE_NAME' => $name,
         'VALUE_RID' => $rid,
         'TR_NEXT' => tr('Next'),
         'TR_PREVIOUS' => tr('Previous')));

gen_select_lists($tpl, $month, $year);
generate_page($tpl, $rid, $name);

generatePageMessage($tpl);

$tpl->parse('PAGE', 'page');

iMSCP_Events_Manager::getInstance()->dispatch(
    iMSCP_Events::onResellerScriptEnd, new iMSCP_Events_Response($tpl));

$tpl->prnt();

unsetMessages();
