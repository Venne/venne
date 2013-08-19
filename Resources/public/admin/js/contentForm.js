$(document).ready(function () {

	var nodiac = {'á':'a', 'č':'c', 'ď':'d', 'é':'e', 'ě':'e', 'í':'i', 'ň':'n', 'ó':'o', 'ř':'r', 'š':'s', 'ť':'t', 'ú':'u', 'ů':'u', 'ý':'y', 'ž':'z'};

	/** Vytvoření přátelského URL
	 * @param string řetězec, ze kterého se má vytvořit URL
	 * @return string řetězec obsahující pouze čísla, znaky bez diakritiky, podtržítko a pomlčku
	 * @copyright Jakub Vrána, http://php.vrana.cz/
	 */
	function make_url(s) {
		s = s.toLowerCase();
		var s2 = '';
		for (var i = 0; i < s.length; i++) {
			s2 += (typeof nodiac[s.charAt(i)] != 'undefined' ? nodiac[s.charAt(i)] : s.charAt(i));
		}
		return s2.replace(/[^a-z0-9_]+/g, '-').replace(/^-|-$/g, '');
	}

	function makeUrl() {
		if ($("#snippet--content input[name='mainPage']").is(':checked')) {
			$("#snippet--content input[name='localUrl']").val("");
			$("#snippet--content input[name='localUrl']").attr("disabled", true);
			$("#snippet--content select[name='parent']").attr("disabled", true);
		} else {
			$("#snippet--content input[name='localUrl']").val(make_url($("#snippet--content input[name='name']").val()));
			$("#snippet--content input[name='localUrl']").attr("disabled", false);
			$("#snippet--content select[name='parent']").attr("disabled", false);
		}
	}


	if ($("#snippet--content input[name='name']").get(0) != undefined) {

		$("#snippet--content input[name='name']").live("change", function () {
			makeUrl();
		});

		$("#snippet--content select[name='parent']").live("change", function () {
			makeUrl();
		});

		$("#snippet--content input[name='mainPage']").live("change", function () {
			makeUrl();
		});
		makeUrl();
	}
});
