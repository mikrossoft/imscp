<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
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
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2011 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 *
 * @category	i-MSCP
 * @package		iMSCP_Core
 * @subpckage	pTemplate
 * @copyright	2001-2006 by moleSoftware GmbH
 * @copyright	2006-2010 by ispCP | http://isp-control.net
 * @copyright	2010 by i-MSCP | http://i-mscp.net
 * @author		VHCS Team
 * @author		ispCP Team
 * @author		i-MSCP Team
 * @link		http://i-mscp.net i-MSCP Home Site
 * @license		http://www.mozilla.org/MPL/ MPL 1.1
 */

/**
 * Class pTemplate is the i-MSCP template engine.
 *
 * @category	i-MSCP
 * @package		iMSCP_Core
 * @subpckage	pTemplate
 * @author		VHCS Team
 * @author		ispCP Team
 * @author		iMSCP Team
 * @todo remove all error operators
 */
class iMSCP_pTemplate
{
	/**
	 * @var array
	 */
	protected $tpl_name = array();

	/**
	 * @var array
	 */
	protected $tpl_data = array();

	/**
	 * @var array
	 */
	protected $tpl_options = array();

	/**
	 * @var array
	 */
	protected $dtpl_name = array();

	/**
	 * @var array
	 */
	protected $dtpl_data = array();

	/**
	 * @var array
	 */
	protected $dtpl_options = array();

	/**
	 * @var array
	 */
	protected $dtpl_values = array();

	/**
	 * @var array
	 */
	protected $namespace = array();

	/**
	 * @var string
	 */
	protected $root_dir;

	/**
	 * @var string
	 */
	protected $tpl_start_tag = '<!-- ';

	/**
	 * @var string
	 */
	protected $tpl_end_tag = ' -->';

	/**
	 * @var string
	 */
	protected $tpl_start_tag_name = 'BDP: ';

	/**
	 * @var string
	 */
	protected $tpl_end_tag_name = 'EDP: ';

	/**
	 * @var string
	 */
	protected $tpl_name_rexpr = '([a-z0-9][a-z0-9\_]*)';

	/**
	 * @var string
	 */
	protected $tpl_start_rexpr;

	/**
	 * @var string
	 */
	protected $tpl_end_rexpr;

	/**
	 * @var string
	 */
	protected $last_parsed = '';

	/**
	 * @var array
	 */
	protected $stack = array();

	/**
	 * @var int
	 */
	protected $sp = 0;

	/**
	 * @var string
	 */
	protected $tpl_include = 'INCLUDE "([^\"]+)"';

	/**
	 * Constructor
	 *
	 * @param string $rootDir Folder where the template files are located
	 */
	public function __construct($rootDir = '')
	{
		if ($rootDir) {
			$this->setRootDir($rootDir);
		} else {
			$this->setRootDir();
		}

		$this->tpl_start_rexpr = '/';
		$this->tpl_start_rexpr .= $this->tpl_start_tag;
		$this->tpl_start_rexpr .= $this->tpl_start_tag_name;
		$this->tpl_start_rexpr .= $this->tpl_name_rexpr;
		$this->tpl_start_rexpr .= $this->tpl_end_tag . '/';

		$this->tpl_end_rexpr = '/';
		$this->tpl_end_rexpr .= $this->tpl_start_tag;
		$this->tpl_end_rexpr .= $this->tpl_end_tag_name;
		$this->tpl_end_rexpr .= $this->tpl_name_rexpr;
		$this->tpl_end_rexpr .= $this->tpl_end_tag . '/';

		$this->tpl_include = '~' .
							 $this->tpl_start_tag .
							 $this->tpl_include .
							 $this->tpl_end_tag .
							 '~m';
	}

	/**
	 * Sets root direcotry.
	 *
	 * @param string $rootDir
	 * @return void
	 */
	private function setRootDir($rootDir = '.')
	{
		$this->root_dir = $rootDir;
	}

	/**
	 * @param  string|array $namespaces Namespace(s)
	 * @param string $namespacesData
	 * @return void
	 */
	public function assign($namespaces, $namespacesData = '')
	{
		if (is_array($namespaces)) {
			foreach ($namespaces as $name => $data) {
				$this->namespace[$name] = $data;
			}
		} else {
			$this->namespace[$namespaces] = $namespacesData;
		}
	}

	/**
	 *
	 * @param  string|array $namespaces
	 * @return void
	 */
	public function unsign($namespaces)
	{
		if (is_array($namespaces)) {
			foreach ($namespaces as $key => $value) {
				unset($this->namespace[$key]);
			}
		} else {
			unset($this->namespace[$namespaces]);
		}
	}

	/**
	 *
	 * @param  string|array $t_name
	 * @param string $t_value
	 * @return void
	 */
	public function define($t_name, $t_value = '')
	{
		if (gettype($t_name) == 'array') {
			foreach ($t_name as $key => $value) {
				$this->tpl_name[$key] = $value;
				$this->tpl_data[$key] = '';
				$this->tpl_options[$key] = '';
			}
		} else {
			$this->tpl_name[$t_name] = $t_value;
			$this->tpl_data[$t_name] = '';
			$this->tpl_options[$t_name] = '';
		}
	}

	/**
	 * @param  $t_name
	 * @param string $t_value
	 * @return void
	 */
	public function define_dynamic($t_name, $t_value = '')
	{
		if (is_array($t_name)) {
			foreach ($t_name as $key => $value) {
				$this->dtpl_name[$key] = $value;
				$this->dtpl_data[$key] = '';
				$this->dtpl_options[$key] = '';
			}
		} else {
			$this->dtpl_name[$t_name] = $t_value;
			$this->dtpl_data[$t_name] = '';
			$this->dtpl_options[$t_name] = '';
		}
	}

	/**
	 * @param  $t_name
	 * @param string $t_value
	 * @return void
	 */
	public function define_no_file($t_name, $t_value = '')
	{
		if (is_array($t_name)) {
			foreach ($t_name as $key => $value) {
				$this->tpl_name[$key] = '_no_file_';
				$this->tpl_data[$key] = $value;
				$this->tpl_options[$key] = '';
			}
		} else {
			$this->tpl_name[$t_name] = '_no_file_';
			$this->tpl_data[$t_name] = $t_value;
			$this->tpl_options[$t_name] = '';
		}
	}

	/**
	 * @param  $t_name
	 * @param string $t_value
	 * @return void
	 */
	public function define_no_file_dynamic($t_name, $t_value = '')
	{
		if (is_array($t_name)) {
			foreach ($t_name as $key => $value) {
				$this->dtpl_name[$key] = '_no_file_';
				$this->dtpl_data[$key] = $value;
				$this->dtpl_data[strtoupper($key)] = $value;
				$this->dtpl_options[$key] = '';
			}
		} else {
			$this->dtpl_name[$t_name] = '_no_file_';
			$this->dtpl_data[$t_name] = $t_value;
			$this->dtpl_data[strtoupper($t_name)] = @$t_value;
			$this->dtpl_options[$t_name] = '';
		}
	}

	/**
	 * @param  $data
	 * @param  $spos
	 * @return array|bool
	 */
	public function find_next($data, $spos)
	{
		do {
			$tag_spos = strpos($data, $this->tpl_start_tag, $spos + 1);

			if (is_bool($tag_spos)) {
				return false;
			}

			$tag_epos = strpos($data, $this->tpl_end_tag, $tag_spos + 1);

			if (is_bool($tag_epos)) {
				return false;
			}

			$length = $tag_epos + strlen($this->tpl_end_tag) - $tag_spos;

			$tag = substr($data, $tag_spos, $length);

			if ($tag) {
				if (preg_match($this->tpl_start_rexpr, $tag, $matches)) {
					return array($matches[1], 'b', $tag_spos,
								 $tag_epos + strlen($this->tpl_end_tag) - 1);
				} else if (preg_match($this->tpl_end_rexpr, $tag, $matches)) {
					return array($matches[1], 'e', $tag_spos,
								 $tag_epos + strlen($this->tpl_end_tag) - 1);
				} else {
					$spos = $tag_epos;
				}
			} else {
				return false;
			}
		} while (true);
	}

	/**
	 * Finds the next pair of curly brakets.
	 *
	 * @param  $data
	 * @param  $spos
	 * @return array|bool
	 */
	private function find_next_curl($data, $spos)
	{
		$curl_b = strpos($data, '{', $spos + 1);
		$curl_e = strpos($data, '}', $spos + 1);

		if ($curl_b) {
			if ($curl_e) {
				if ($curl_b < $curl_e) {
					return array('{', $curl_b);
				} else {
					return array('}', $curl_e);
				}
			} else {
				return array('{', $curl_b);
			}
		} else {
			if ($curl_e) {
				return array('}', $curl_e);
			} else {
				return false;
			}
		}
	}

	/**
	 *
	 * @param  $data
	 * @return mixed
	 */
	private function devide_dynamic($data)
	{
		$start_from = -1;

		$tag = $this->find_next($data, $start_from);

		while ($tag) {
			if ($tag[1] == 'b') {
				$this->stack[$this->sp++] = $tag;

				$start_from = $tag[3];
			} else {
				$tpl_name = $tag[0];
				$tpl_eb_pos = $tag[2];
				$tpl_ee_pos = $tag[3];
				$tag = $this->stack [--$this->sp];
				$tpl_bb_pos = $tag[2];
				$tpl_be_pos = $tag[3];

				$this->dtpl_data[strtoupper($tpl_name)] = substr($data, $tpl_be_pos + 1,
																 $tpl_eb_pos - $tpl_be_pos - 1);

				$this->dtpl_data[$tpl_name] = substr($data, $tpl_be_pos + 1,
													 $tpl_eb_pos - $tpl_be_pos - 1);

				$data = substr_replace($data, '{' . strtoupper($tpl_name) . '}',
									   $tpl_bb_pos, $tpl_ee_pos - $tpl_bb_pos + 1);

				$start_from = $tpl_bb_pos + strlen("{" . $tpl_name . "}") - 1;
			}

			$tag = $this->find_next($data, $start_from);
		}

		return $data;
	}

	/**
	 * @param  $data
	 * @return mixed
	 */
	private function substitute_dynamic($data)
	{
		$this->sp = 0;
		$start_from = -1;
		$curl_b = substr($data, (int)'{', $start_from);

		if ($curl_b) {
			$this->stack[$this->sp++] = array('{', $curl_b);
			$curl = $this->find_next_curl($data, $start_from);

			while ($curl) {
				if ($curl[0] == '{') {
					$this->stack[$this->sp++] = $curl;
					$start_from = $curl[1];
				} else {
					$curl_e = $curl[1];

					if ($this->sp > 0) {
						$curl = $this->stack [--$this->sp];
						// CHECK for empty stack must be done HERE !
						$curl_b = $curl[1];

						if ($curl_b < $curl_e + 1) {
							$var_name = substr($data, $curl_b + 1, $curl_e - $curl_b - 1);

							// The whole WORK goes here :)
							if (preg_match('/[A-Z0-9][A-Z0-9\_]*/', $var_name)) {
								if (isset($this->namespace[$var_name])) {
									$data = substr_replace($data,
														   $this->namespace[$var_name],
														   $curl_b, $curl_e - $curl_b + 1);

									$start_from = $curl_b - 1;
									// new value may also begin with '{'

								} else if (isset($this->dtpl_data[$var_name])) {
									$data = substr_replace($data,
														   $this->dtpl_data[$var_name],
														   $curl_b, $curl_e - $curl_b + 1);
									$start_from = $curl_b - 1;
									// new value may also begin with '{'
								} else {
									$start_from = $curl_b;
									// no suitable value found -> go forward
								}
							} else {
								$start_from = $curl_b;
								// go forward, we have {no variable} here.
							}
						} else {
							$start_from = $curl_e;
							// go forward, we have {} here.
						}
					} else {
						$start_from = $curl_e;
					}
				}
				$curl = $this->find_next_curl($data, $start_from);
			}

			return $data;
		} else {
			return $data;
			// there is nothing to substitute in $data
		}
	}

	/**
	 * @param  $fname
	 * @return bool
	 */
	private function is_safe($fname)
	{
		return (file_exists(($this->root_dir) . '/' . $fname)) ? true : false;
	}

	/**
	 * Checks if a namespace is defined.
	 *
	 * @author Laurent Declercq <l.declercq@nuxwin.com>
	 * @since r2467
	 * @param string $namespace namespace
	 * @return boolean TRUE if the namespace was define, FALSE otherwise
	 */
	public function is_namespace($namespace)
	{
		return in_array($namespace, $this->namespace);
	}

	/**
	 * Load a template file.
	 *
	 * @throws iMSCP_Exception If template file is not found
	 * @param string|array $fname Template file path or an array where the second
	 *							   item contain the template file path
	 * @return mixed|string
	 */
	public function get_file($fname)
	{
		static $parentTplDir = null;

		if (!is_array($fname)) {
			iMSCP_Events_Manager::getInstance()->dispatch(
				iMSCP_pTemplate_Events::onBeforeAssembleTemplateFiles, $fname);
		} else { // INCLUDED file
			$fname = ($parentTplDir !== null) ? $parentTplDir . '/' . $fname[1]
				: $fname[1];
		}

		if ($this->is_safe($fname)) {
			$prevParentTplDir = $parentTplDir;
			$parentTplDir = dirname($fname);

			iMSCP_Events_Manager::getInstance()->dispatch(
				iMSCP_pTemplate_Events::onBeforeLoadTemplateFile, $fname);

			$fileContent = file_get_contents($this->root_dir . '/' . $fname);

			iMSCP_Events_Manager::getInstance()->dispatch(
				iMSCP_pTemplate_Events::onAfterLoadTemplateFile, $fileContent);

			$fileContent = preg_replace_callback(
				$this->tpl_include, array($this, 'get_file'), $fileContent);

			$parentTplDir = $prevParentTplDir;
		} else {
			throw new iMSCP_Exception(sprintf('Unable to find the %s template file', $fname));
		}

		iMSCP_Events_Manager::getInstance()->dispatch(
			iMSCP_pTemplate_Events::onAfterAssembleTemplateFiles, $fileContent);

		return $fileContent;
	}

	/**
	 * @param  $tname
	 * @return bool
	 */
	private function find_origin($tname)
	{
		if (!@$this->dtpl_name[$tname]) {
			return false;
		}

		while (!preg_match('/\.[Tt][Pp][Ll]/', $this->dtpl_name[$tname])
			   && !preg_match('/_no_file_/', $this->dtpl_name[$tname])
		) {
			$tname = $this->dtpl_name[$tname];
		}

		return $tname;
	}

	/**
	 * @param  $pname
	 * @param  $tname
	 * @param  $ADD_FLAG
	 * @return bool
	 */
	public function parse_dynamic($pname, $tname, $ADD_FLAG)
	{
		$CHILD = false;
		$parent = '';
		//$swap = '';

		if (!preg_match('/\.[Tt][Pp][Ll]/', @$this->dtpl_name[$tname])
			&& !preg_match('/_no_file_/', @$this->dtpl_name[$tname])
		) {
			$CHILD = true;

			$parent = $this->find_origin($tname);

			if (!$parent) {
				return false;
			}
		}

		if ($CHILD) {
			$swap = $parent;
			$parent = $tname;
			$tname = $swap;
		}

		if (!@$this->dtpl_data[$tname]) {
			@$this->dtpl_data[$tname] = $this->get_file(@$this->dtpl_name[$tname]);
		}

		if (!preg_match('/d\_/', @$this->dtpl_options[$tname])) {
			@$this->dtpl_options[$tname] .= 'd_';

			$tpl_origin = @$this->dtpl_data[$tname];

			@$this->dtpl_data[$tname] = $this->devide_dynamic($tpl_origin);
		}

		if ($CHILD) {
			$swap = $parent;
			//$parent = $tname;
			$tname = $swap;
		}

		if ($ADD_FLAG) {
			$safe = @$this->namespace[$pname];

			$this->namespace[$pname] = $safe . ($this->substitute_dynamic($this->dtpl_data[$tname],
																		  $ADD_FLAG));
		} else {
			$this->namespace[$pname] = $this->substitute_dynamic($this->dtpl_data[$tname],
																 $ADD_FLAG);
		}

		return true;
	}

	/**
	 * @param  $pname
	 * @param  $tname
	 * @return bool
	 */
	public function parse($pname, $tname)
	{
		if (!preg_match('/[A-Z0-9][A-Z0-9\_]*/', $pname)) {
			return false;
		}

		if (!preg_match('/[A-Za-z0-9][A-Za-z0-9\_]*/', $tname)) {
			return false;
		}

		$ADD_FLAG = false;

		if (preg_match('/^\./', $tname)) {
			$tname = substr($tname, 1);

			$ADD_FLAG = true;
		}

		if (@$this->tpl_name[$tname] == '_no_file_'
			 || preg_match('/\.[Tt][Pp][Ll]/', @$this->tpl_name[$tname])
		) {
			// static NO FILE - static FILE

			if (@$this->tpl_data[$tname] == '') {
				$this->tpl_data[$tname] = $this->get_file($this->tpl_name[$tname]);
			}

			if ($ADD_FLAG) {
				@$this->namespace[$pname] .= $this->substitute_dynamic($this->tpl_data[$tname]);
			} else {
				$this->namespace[$pname] = $this->substitute_dynamic($this->tpl_data[$tname]);
			}

			$this->last_parsed = $this->namespace[$pname];
		} elseif (@$this->dtpl_name[$tname] == '_no_file_'
				   || preg_match('/\.[Tt][Pp][Ll]/', @$this->dtpl_name[$tname])
				   || $this->find_origin($tname)
		) {
			// dynamic NO FILE - dynamic FILE
			$dres = $this->parse_dynamic($pname, $tname, $ADD_FLAG);

			if (!$dres) {
				return $dres;
			}

			$this->last_parsed = $this->namespace[$pname];
		} else {
			if ($ADD_FLAG) {
				@$this->namespace[$pname] .= $this->namespace[$tname];
			} else {
				$this->namespace[$pname] = $this->namespace[$tname];
			}
		}
	}

	/**
	 * @param string $pname
	 * @return void
	 */
	public function prnt($pname = '')
	{
		if ($pname) {
			echo preg_replace('/^\n+|^[\t\s]*\n+/m', '', @$this->namespace[$pname]);
		} else {
			echo preg_replace('/^\n+|^[\t\s]*\n+/m', '', @$this->last_parsed);
		}
	}

	/**
	 * @param string $pname
	 * @return void
	 */
	public function FastPrint($pname = '')
	{
		if ($pname) {
			$this->prnt($pname);
		} else {
			$this->prnt();
		}
	}

	/**
	 * Returns last parse result.
	 *
	 * @return string
	 */
	public function getLastParseResult()
	{
		return $this->last_parsed;
	}

	/**
	 * Replaces last parse result with given content.
	 *
	 * @param string $newContent New content
	 * @return iMSCP_pTemplate Provides fluent interface, returns self
	 */
	public function replaceLastParseResult($newContent)
	{
		$this->last_parsed = (string)$newContent;

		return $this;
	}

	/**
	 * @return void
	 */
	/*
		public function print_namespace()
		{
			print "<br><u>'namespace' contents</u><br>";

			foreach ($this->namespace as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_tpl_name()
		{
			print "<br><u>'tpl_name' contents</u><br>";

			foreach ($this->tpl_name as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_dtpl_name()
		{
			print "<br><u>'dtpl_name' contents</u><br>";

			foreach ($this->dtpl_name as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_tpl_data()
		{
			print "<br><u>'tpl_data' contents</u><br>";

			foreach ($this->tpl_data as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_dtpl_data()
		{
			print "<br><u>'dtpl_data' contents</u><br>";

			foreach ($this->dtpl_data as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_dtpl_options()
		{
			print "<br><u>'dtpl_options' contents</u><br>";

			foreach ($this->dtpl_options as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/

	/**
	 * @return void
	 */
	/*
		public function print_dtpl_values()
		{
			print "<br><u>'dtpl_values' contents</u><br>";

			foreach ($this->dtpl_values as $key => $value) {
				print "$key => $value<br>";
			}
		}
		*/
}
