<!-- Common page footer -->


<footer id="site_footer" class="row">
	<span class="col-12 center-text"> Copyright &copy; Tudor Marcu </span>
</footer>

<script type="text/javascript">
function elementIsFloating(elem){
	var wHeight = window.innerHeight;
	var elemPos = elem.getBoundingClientRect();

	//If the top of the bounding rect is less than 0
	//Or the bottom is more than wHeight then the element is not fully visible

	return elemPos.bottom <= wHeight;
}

//Run this once on page load

fixFooter();
window.addEventListener("resize", fixFooter);

function fixFooter(){
	var ftr = document.getElementById("site_footer");
	ftr.style.position = "static";
	if (elementIsFloating(ftr)){
		//move the footer to the bottom
		ftr.style.position = "absolute";
	} else {
		ftr.style.position = "static";
	}
}
</script>

