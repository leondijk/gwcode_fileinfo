# GWcode FileInfo
## Free plugin for ExpressionEngine 2.0+
#### By Leon Dijk - [@GWcode](http://twitter.com/#!/gwcode)
v1.0.1
### Description

Get information about files on your server.

### TODO

* Create add-on pages on gwcode.com
* Add to devot-ee.com

### Documentation

For full documentation and examples, please visit:  
[http://gwcode.com/add-ons/gwcode-fileinfo](http://gwcode.com/add-ons/gwcode-fileinfo)

### Installation

* Upload the /system/expressionengine/third_party/gwcode_fileinfo/ folder to /system/expressionengine/third_party/
* Check if the plugin is listed when you go to Add-Ons &rarr; Plugins in your EE Control Panel.

### Examples

###### 1. Get information about a single file

	{exp:gwcode_fileinfo:single file="/media/image.jpg"}
		{if no_results}File not found!{/if}
		File full path: {file_fullpath}<br />
		File URL: {file_url}<br />
		File name: {file_name}<br />
		File basename: {file_basename}<br />
		File extension: {file_extension}<br />
		File extension mime: {file_extension_mime}<br />
		File size in bytes: {file_size_bytes}<br />
		File size formatted: {file_size_formatted}<br />
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

### Support and Feature Requests
Please post on the @devot_ee forums:  
[http://devot-ee.com/add-ons/gwcode-fileinfo/](http://devot-ee.com/add-ons/gwcode-fileinfo/)

### License
This plugin is licensed under The BSD 3-Clause License:  
[http://www.opensource.org/licenses/bsd-3-clause](http://www.opensource.org/licenses/bsd-3-clause)

Copyright (c) 2012 Leon Dijk  
[http://gwcode.com](http://gwcode.com)