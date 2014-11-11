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
				fwrite($UML_HTML_file,"        <p>+ $var_data[1]: $var_data[0]</p>\n");
			}
			foreach ($data->private_var as $var_name) {
				$var_data = split(' ->',$var_name);
				fwrite($UML_HTML_file,"        <p><u>- $var_data[1]: $var_data[0]</u></p>\n");
			}
			
			fwrite($UML_HTML_file,"    </div>\n\n    <div style='width: 400px'>\n        <hr>\n    </div>\n\n");
			
			fwrite($UML_HTML_file,"    <div id='method' style='margin-left: 10px'>\n");
			
			for ($p = 0;$p < count($data->methods);$p++) {
				$tmp = $data->methods;
				$method = $tmp[$p];
				$param_array = split(' ->',$method->params);
				$method_name = $method->name;
				if ($param_array[0] == 'public') {
					fwrite($UML_HTML_file,"        <p>+ $method_name(");
				}else {
					fwrite($UML_HTML_file,"        <p><u>- $method_name(");
				}
				$params = @split(',',$param_array[1]);
				$method_return = "";
				if (strlen($params[0]) != 0) {
					for ($index = 0; $index < count($params); $index++) {
						$single_param = trim($params[$index]);
						preg_match("/(\w+( +)?((\[\]){0,}|(...)))( +)?(\w+)/",$single_param,$matches);
						$param_name = @$matches[count($matches)-1];
						$param_type = @trim($matches[1]);
						if ($index == count($params)-1 || count($params) == 2) {
							if ($param_type == "return") {
								$method_return = $param_name;
								continue;
							}
							fwrite($UML_HTML_file,"$param_name: $param_type");
						}else {
							if ($param_type == "return") {
								$method_return = $param_name;
								continue;
							}
							fwrite($UML_HTML_file,"$param_name: $param_type, ");
						}
					}
				}
				if ($method_return == "nbsp") {
					$method_return = "";
				}
				if (strlen($method_return) > 0) {
					$method_return = " : $method_return";
				}
				if ($param_array[0] == 'public') fwrite($UML_HTML_file,")$method_return</p>\n");
				else fwrite($UML_HTML_file,")$method_return</u></p>\n");
			}
			fwrite($UML_HTML_file,"    </div>");
			$first = false;
		}
		fwrite($UML_HTML_file,"\n</body>\n<html>\n");
		fclose($UML_HTML_file);
		$content = file_get_contents("$filename.html");
		$content = str_replace(", )",")",$content);
		$UML_HTML_file = fopen("$filename.html","w");
		fwrite($UML_HTML_file,$content);
		fclose($UML_HTML_file);
	}
	
?>