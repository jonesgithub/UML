<?php
	for ($i = 1; $i < count($argv); $i++) {
		$json   = json_decode(file_get_contents($argv[$i]));
		$filename =  basename($argv[$i],".UMLtmp");
		$UML_HTML_file = fopen("$filename.html","w");
		fwrite($UML_HTML_file,"<!DOCTYPE html>\n<html>\n<body>\n    <div id='UML'>\n");
		$first = true;
		foreach ($json as $class => $data) {
			if ($first == false) {
				fwrite($UML_HTML_file,"    <div style='margin-top: 40px'></div>\n");
			}
			fwrite($UML_HTML_file,"    <div style='width: 400px;height: 19px;background-color: #090909'>\n        <center><font color='white'>$class</font></center>\n    </div>\n");
			fwrite($UML_HTML_file,"\n    <div id='var' style='margin-left: 10px'>\n");
			foreach ($data->public_var as $var_name) {
				$var_data = split(' ->',$var_name);
				fwrite($UML_HTML_file,"        <p>$var_data[1]: $var_data[0]</p>\n");
			}
			foreach ($data->private_var as $var_name) {
				$var_data = split(' ->',$var_name);
				fwrite($UML_HTML_file,"        <p><u>$var_data[1]: $var_data[0]</u></p>\n");
			}
			
			fwrite($UML_HTML_file,"    </div>\n\n    <div style='width: 400px'>\n        <hr>\n    </div>\n\n");
			
			fwrite($UML_HTML_file,"    <div id='method' style='margin-left: 10px'>\n");
			foreach ($data->methods as $method_name => $paramters) {
				$param_array = split(' ->',$paramters);
				if ($param_array[0] == 'public') {
					fwrite($UML_HTML_file,"        <p>$method_name(");
				}else {
					fwrite($UML_HTML_file,"        <p><u>$method_name(");
				}
				$params = @split(',',$param_array[1]);
				if (strlen($params[0]) != 0) {
					for ($index = 0; $index < count($params); $index++) {
						$single_param = trim($params[$index]);
						preg_match("/(\w+( +)?((\[\]){0,}|(...)))( +)?(\w+)/",$single_param,$matches);
						$param_name = @$matches[count($matches)-1];
						$param_type = @trim($matches[1]);
						if ($index == count($params)-1 || count($params) == 1) {
							fwrite($UML_HTML_file,"$param_name: $param_type");
						}else {
							fwrite($UML_HTML_file,"$param_name: $param_type, ");
						}
					}
				}
				if ($param_array[0] == 'public') fwrite($UML_HTML_file,")</p>\n");
				else fwrite($UML_HTML_file,")</u></p>\n");
			}
			fwrite($UML_HTML_file,"    </div>");
			$first = false;
		}
		fwrite($UML_HTML_file,"\n</body>\n<html>\n");
		fclose($UML_HTML_file);
	}
	
?>
