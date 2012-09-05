# GWcode FileInfo
## Free plugin for ExpressionEngine 2.0+
#### By Leon Dijk - [@GWcode](http://twitter.com/#!/gwcode)
v1.0.3
### Description

Get information about files on your server.

### Documentation

For full documentation and examples, please visit:  
[http://gwcode.com/add-ons/gwcode-fileinfo](http://gwcode.com/add-ons/gwcode-fileinfo)

### Installation

* Upload the /system/expressionengine/third_party/gwcode_fileinfo/ folder to /system/expressionengine/third_party/
* Check if the plugin is listed when you go to Add-Ons &rarr; Plugins in your EE Control Panel.

### Examples

###### 1. Get information about a single file

	{exp:gwcode_fileinfo:single file="/media/myimage.jpg"}
		{if file_not_found}The file couldn't be found!{/if}
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

###### Example output:

> File full path: /full/server/path/to/docroot/media/my_image.jpg  
> File URL: http://domain.tld/media/my_image.jpg  
> File name: my_image.jpg  
> File basename: my_image  
> File extension: jpg  
> File extension mime: image/jpeg  
> File size in bytes: 741843  
> File size formatted: 724.46 KB  
> File symbolic permissions: -rw-r--r--  
> File octal permissions: 644  
> File is image: Yes  
> Image width: 1345  
> Image height: 1174  
> Image bits: 8  
> Image channels: 3  
> Image mime: image/jpeg

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

###### 3. Get information about files stored in a matrix field

	{exp:channel:entries channel="my_channel" limit="1"}
		<h3>{title}</h3>
		<h4>Files stored in our matrix field (cf_matrix_gallery):</h4>
		{cf_matrix_gallery}
			{exp:gwcode_fileinfo:single file="{cf_matrix_gallery_yourmatrixcolumn}"}
				{if file_not_found}The file "{cf_matrix_gallery_yourmatrixcolumn}" could not be found!{/if}
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
			<br />
		{/cf_matrix_gallery}
	{/exp:channel:entries}

### Support and Feature Requests
Please post on the @devot_ee forums:  
[http://devot-ee.com/add-ons/gwcode-fileinfo/](http://devot-ee.com/add-ons/gwcode-fileinfo/)

### License
This plugin is licensed under The BSD 3-Clause License:  
[http://www.opensource.org/licenses/bsd-3-clause](http://www.opensource.org/licenses/bsd-3-clause)

Copyright (c) 2012 Leon Dijk  
[http://gwcode.com](http://gwcode.com)