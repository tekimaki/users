{strip}
<div class="usersurvey register">
	<div class="header">
		<h1>{tr}Thank you!{/tr}</h1>
	</div>
	<div class="body">
		<p>{tr}You are now a registered member. We have sent you an email with your account details. Please make a note of your username and password for future use. Welcome!{/tr}</p>
		<p>
			{tr}What would you like to do now?{/tr}<br />
			<strong>{smartlink ipackage=opportunities ifile=my_opps_prefs.php ititle="Set up a volunteer profile"}</strong> <img src="{$smarty.const.THEMES_STYLE_URL}images/right_arrow_whitematte.gif" alt="{tr}go{/tr}" /><br />
			<strong>{smartlink ipackage=users ifile=my.php ititle="Go to my account page"}</strong> <img src="{$smarty.const.THEMES_STYLE_URL}images/right_arrow_whitematte.gif" alt="{tr}go{/tr}" />
		</p>
	</div>
</div>
{/strip}
{literal}
<script type="text/javascript">
document.observe("dom:loaded", function() {
	try { 
		pageTracker._trackPageview('register>volunteer_complete');
	} catch(err) {}
});
</script>
{/literal}
