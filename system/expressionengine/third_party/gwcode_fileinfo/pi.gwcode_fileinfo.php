<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
============================================================
 Created by Leon Dijk
 - http://gwcode.com/
------------------------------------------------------------
 This plugin is licensed under The BSD 3-Clause License.
 - http://www.opensource.org/licenses/bsd-3-clause
============================================================
*/

$plugin_info = array(
	'pi_name'			=> 'GWcode FileInfo',
	'pi_version'		=> '1.0.4',
	'pi_author'			=> 'Leon Dijk',
	'pi_author_url'		=> 'http://gwcode.com/add-ons/gwcode-fileinfo',
	'pi_description'	=> 'Get information about files on your server.',
	'pi_usage'			=> gwcode_fileinfo::usage()
);

class Gwcode_fileinfo {
	private $tagdata = '';
	private $docroot_path = '';
	private $var_prefix = '';

	// create the var_values_arr array that holds the information we need for a file
	private $var_values_arr = array(
		'file_fullpath' => '',
		'file_url' => '',
		'file_name' => '',
		'file_basename' => '',
		'file_extension' => '',
		'file_extension_mime' => '',
		'file_size_bytes' => '',
		'file_size_formatted' => '',
		'file_symbolic_permissions' => '',
		'file_octal_permissions' => '',
		'file_is_image' => false,
		'image_width' => '',
		'image_height' => '',
		'image_bits' => '',
		'image_channels' => '',
		'image_mime' => ''
	);

	public function Gwcode_fileinfo() {
		$this->__construct();
	}

	public function __construct() {
		$this->EE =& get_instance();
		$this->var_prefix = $this->EE->TMPL->fetch_param('variable_prefix', '');

		$this->_prep_no_results(); // prepares our own custom no_results block: file_not_found
		$this->tagdata = $this->EE->TMPL->tagdata;

		// load CI and the helpers we need
		$this->CI =& get_instance();
		// http://codeigniter.com/user_guide/helpers/url_helper.html
		// http://codeigniter.com/user_guide/helpers/file_helper.html
		$this->CI->load->helper(array('url', 'file'));

		$this->docroot_path = (array_key_exists('DOCUMENT_ROOT', $_ENV)) ? $_ENV['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'];
		$this->docroot_path = rtrim($this->docroot_path, '/'); // remove trailing slash, if any
	}
  
	/**
	 * EE plugin method to get information about a single file.
	 * Possible parameters: file
	 */
	public function single() {
		$file = trim($this->EE->TMPL->fetch_param('file'));
		if(empty($file)) {
			$this->EE->TMPL->log_item('Error: the "file" parameter value is required.');
			return;
		}

		if(strpos($file, '://') !== false) { // the file value is a URL
			// check if domain from site is equal to domain in the file URL
			$site_url_parsed = parse_url(base_url());
			$file_url_parsed = parse_url($file);
			if(!$this->_equal_domains($site_url_parsed['host'], $file_url_parsed['host'])) {
				$this->EE->TMPL->log_item('Error: the domain in the "file" parameter value appears to be a remote URL.');
				return;
			}

			$fulldomain = $file_url_parsed['scheme'].'://'.$file_url_parsed['host'];
			$file_relative_from_docroot = str_replace($fulldomain, '', $file); // example contents: /media/image.jpg
			$file_full_path = ($file_relative_from_docroot{0} == '/') ? $this->docroot_path.$file_relative_from_docroot : $this->docroot_path.'/'.$file_relative_from_docroot;
		}
		elseif(strpos($file, $this->docroot_path) !== false) { // the file value is a full server path
			$file_full_path = $file;
		}
		else { // the file value is a relative path
			$file_full_path = ($file{0} == '/') ? $this->docroot_path.$file : $this->docroot_path.'/'.$file;
		}

		// now that we have the full path to the file and the file exists, get all the information we need!
		$var_values_arr = $this->_get_file_info($file_full_path);
		if(!$var_values_arr) {
			return $this->EE->TMPL->no_results();
		}
		return $this->EE->TMPL->parse_variables_row($this->tagdata, $var_values_arr);
	}

	/**
	 * EE plugin method to get information for multiple files in a directory.
	 * Possible parameters: directory
	 */
	public function multiple() {
		$directory = trim($this->EE->TMPL->fetch_param('directory'));
		if(empty($directory)) {
			$this->EE->TMPL->log_item('Error: the "directory" parameter value is required.');
			return;
		}
		$directory = rtrim($directory, '/'); // remove trailing slash, if any

		if(strpos($directory, '://') !== false) { // the directory value is a URL
			// check if domain from site is equal to domain in the directory URL
			$site_url_parsed = parse_url(base_url());
			$directory_url_parsed = parse_url($directory);
			if(!$this->_equal_domains($site_url_parsed['host'], $directory_url_parsed['host'])) {
				$this->EE->TMPL->log_item('Error: the domain in the "directory" parameter value appears to be a remote URL.');
				return;
			}

			$fulldomain = $directory_url_parsed['scheme'].'://'.$directory_url_parsed['host'];
			$directory_relative_from_docroot = str_replace($fulldomain, '', $directory); // example contents: /media/image.jpg
			$directory_full_path = ($directory_relative_from_docroot{0} == '/') ? $this->docroot_path.$directory_relative_from_docroot : $this->docroot_path.'/'.$directory_relative_from_docroot;
		}
		elseif(strpos($directory, $this->docroot_path) !== false) { // the file value is a full server path
			$directory_full_path = $directory;
		}
		else { // the file value is a relative path
			$directory_full_path = ($directory{0} == '/') ? $this->docroot_path.$directory : $this->docroot_path.'/'.$directory;
		}

		$var_values_arr = array();

		$this->CI->load->helper('directory');
		$dir_map = directory_map($directory_full_path, 1); // we don't map subdirectories at this time
		foreach($dir_map as $key => $file) {
			if(is_file($directory_full_path.'/'.$file)) {
				// file found, get all the information we need!
				$var_values_arr[] = $this->_get_file_info($directory_full_path.'/'.$file);
			}
		}
		return $this->EE->TMPL->parse_variables($this->tagdata, $var_values_arr);
	}



	/* -------------------- internal functions and usage from here on down -------------------- */

	/**
	 * Get information about a single file and return the results.
	 * @param string $file_full_path
	 * @return array
	 */
	private function _get_file_info($file_full_path) {
		// create the var_values_arr array that's going to hold the information we need
		$var_values_arr = $this->var_values_arr;

		// get some basic file info with the CI file helper
		$file_info_arr = get_file_info($file_full_path, array('name', 'server_path', 'size', 'date', 'fileperms'));
		if(!$file_info_arr) { // file not found
			return false;
		}

		// add file information
		$var_values_arr[$this->var_prefix.'file_fullpath'] = $file_full_path;
		$var_values_arr[$this->var_prefix.'file_url'] = rtrim(base_url(), '/').str_replace($this->docroot_path, '', $file_full_path);
		$var_values_arr[$this->var_prefix.'file_name'] = $file_info_arr['name'];
		$filename_parsed = pathinfo($file_info_arr['name']);
		$var_values_arr[$this->var_prefix.'file_basename'] = $filename_parsed['filename'];
		$var_values_arr[$this->var_prefix.'file_extension'] = $filename_parsed['extension'];
		$var_values_arr[$this->var_prefix.'file_extension_mime'] = get_mime_by_extension($file_full_path); // if for example a .jpg file has been renamed to .gif, this value will be 'image/gif'
		$var_values_arr[$this->var_prefix.'file_size_bytes'] = $file_info_arr['size'];
		$var_values_arr[$this->var_prefix.'file_size_formatted'] = $this->_filesize_format($file_info_arr['size']);
		$var_values_arr[$this->var_prefix.'file_symbolic_permissions'] = symbolic_permissions($file_info_arr['fileperms']);
		$var_values_arr[$this->var_prefix.'file_octal_permissions'] = octal_permissions($file_info_arr['fileperms']);

		// add image information
		if($this->_is_image($var_values_arr[$this->var_prefix.'file_extension_mime'])) {
			$var_values_arr[$this->var_prefix.'file_is_image'] = true;
			$imagesize_arr = getimagesize($file_full_path);
			$var_values_arr[$this->var_prefix.'image_width'] = $imagesize_arr[0];
			$var_values_arr[$this->var_prefix.'image_height'] = $imagesize_arr[1];
			$var_values_arr[$this->var_prefix.'image_bits'] = $imagesize_arr['bits'];
			$var_values_arr[$this->var_prefix.'image_channels'] = $imagesize_arr['channels'];
			$var_values_arr[$this->var_prefix.'image_mime'] = $imagesize_arr['mime']; // if for example a .jpg file has been renamed to .gif, this value will be 'image/jpeg' (ie, the real mime type)
		}

		return $var_values_arr;
	}

	/**
	 * Check if a file is an image, based on the extension
	 * @param string $mime
	 * @return bool
	 */
	private function _is_image($mime) {
		return (strpos($mime, 'image') !== false) ? true : false;
	}

	/**
	 * Check if 2 domains without www match (ie, www.domain.tld will match domain.tld, but not differentdomain.tld)
	 * @param string $domain1, $domain2
	 * @return bool
	 */
	private function _equal_domains($domain1, $domain2) {
		$domain1 = preg_replace('#^www\.(.+\.)#i', '$1', $domain1);
		$domain2 = preg_replace('#^www\.(.+\.)#i', '$1', $domain2);
		return ($domain1 == $domain2);
	}

	/**
	 * Format file size
	 * @param string $size
	 * @return string
	 */
	private function _filesize_format($size) {
		if($size == 0) {
			return('n/a');
		}
		$sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $sizes[$i]);
	}

	/**
	 * Sadly, using {if no_results} doesn't work when nesting GWcode FileInfo in a matrix tag for example due to a bug in EE's template parser.
	 * So instead, we're going to use a custom no_results block, file_not_found, which will act the same as no_results.
	 * Usage: {if file_not_found}The file couldn't be found!{/if}
	 * See http://experienceinternet.co.uk/blog/ee-gotchas-nested-no-results-tags-redux/
	 */
	private function _prep_no_results() {
		// Shortcut to tagdata
		$td =& $this->EE->TMPL->tagdata;
		$open  = 'if '.$this->var_prefix.'file_not_found';
		$close = '/if';

		// Check if there is a custom no_results conditional
		if(strpos($td, $open) !== FALSE && preg_match('#' .LD .$open .RD .'(.*?)' .LD .$close .RD .'#s', $td, $match)) {
			$this->EE->TMPL->log_item("Prepping {$open} conditional");
			// Check if there are conditionals inside of that
			if(stristr($match[1], LD.'if')) {
				$match[0] = $this->EE->functions->full_tag($match[0], $td, LD.'if', LD.'\/if'.RD);
			}
			// Set template's no_results data to found chunk
			$this->EE->TMPL->no_results = substr($match[0], strlen(LD.$open.RD), -strlen(LD.$close.RD));
			// Remove no_results conditional from tagdata
			$td = str_replace($match[0], '', $td);
		}
	}

	/**
	 * Describes how the plugin is used.
	 */
	public function usage() {
		ob_start();
?>
###### 1. Get information about a single file

	{exp:gwcode_fileinfo:single file="/media/image.jpg"}
		{if file_not_found}The file couldn't be found!<br />{/if}
		File full path: {file_fullpath}<br />
		File URL: {file_url}<br />
		File name: {file_name}<br />
		File basename: {file_basename}<br />
		File extension: {file_extension}<br />
		File extension mime: {file_extension_mime}<br />
		File size in bytes: {file_size_bytes}<br />
		File size formatted: {file_size_formatted}<br />
		File symbolic permissions: {file_symbolic_permissions}<br />
		File octal permissions: {file_octal_permissions}<br />
		File is image: {if file_is_image}Yes{if:else}No{/if}<br />
		{if file_is_image}
			Image width: {image_width}<br />
			Image height: {image_height}<br />
			Image bits: {image_bits}<br />
			Image channels: {image_channels}<br />
			Image mime: {image_mime}<br />
		{/if}
	{/exp:gwcode_fileinfo:single}

###### 2. Get information about files in a directory

	{exp:gwcode_fileinfo:multiple directory="/path/to/media/"}
		Count: {count}<br />
		Total results: {total_results}<br />
		Switch: {switch="uneven|even"}<br />
		File full path: {file_fullpath}<br />
		File URL: {file_url}<br />
		File name: {file_name}<br />
		File basename: {file_basename}<br />
		File extension: {file_extension}<br />
		File extension mime: {file_extension_mime}<br />
		File size in bytes: {file_size_bytes}<br />
		File size formatted: {file_size_formatted}<br />
		File symbolic permissions: {file_symbolic_permissions}<br />
		File octal permissions: {file_octal_permissions}<br />
		File is image: {if file_is_image}Yes{if:else}No{/if}<br />
		{if file_is_image}
			Image width: {image_width}<br />
			Image height: {image_height}<br />
			Image bits: {image_bits}<br />
			Image channels: {image_channels}<br />
			Image mime: {image_mime}<br />
		{/if}
		<br />
	{/exp:gwcode_fileinfo:multiple}
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

} // end class: Gwcode_fileinfo
?>