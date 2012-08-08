$(document).ready(function () {

	var inputs = new Array();
	inputs["pdo_mysql"] = ["host", "port", "user", "password", "dbname", "charset", "collation"];
	inputs["pdo_pgsql"] = ["host", "port", "user", "password", "dbname"];
	inputs["pdo_oci"] = ["host", "port", "user", "password", "dbname", "charset"];
	inputs["pdo_sqlsrv"] = ["host", "port", "user", "password", "dbname"];
	inputs["pdo_sqlite"] = ["user", "password", "path", "memory"];
	inputs["oci8"] = inputs["pdo_oci"];

	function setItems(){
		$("#frm-systemDatabaseForm input").each(function(){

			if($(this).attr("name") == "test" || $(this).attr("name") == "_submit" || $(this).attr("name") == "_cancel"){
				return ;
			}

			$(this).parent().parent().hide();
		});
		$("#frm-systemDatabaseForm select").each(function(){

			if($(this).attr("name") == "driver"){
				return ;
			}

			$(this).parent().parent().hide();
		});

		var driver = $("#frmsystemDatabaseForm-driver option:selected").val();

		if(inputs[driver] != undefined){
			for (var key in inputs[driver]) {
				$("#frmsystemDatabaseForm-" + inputs[driver][key]).parent().parent().show();
			}
		}
	}

	setItems();
	$("#frmsystemDatabaseForm-driver").live("change", setItems);

});
