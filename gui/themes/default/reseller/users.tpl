<!-- INCLUDE "../shared/layout/header.tpl" -->
		<script type="text/javascript">
			/* <![CDATA[ */
			function action_status(dom_id, dmn_name) {
				if (!confirm(sprintf("{TR_MESSAGE_CHANGE_STATUS}", dmn_name))) {
					return false;
				}

				location = ("domain_status_change.php?domain_id=" + dom_id);
			}
			/* ]]> */
		</script>
		<div class="header">
			{MAIN_MENU}
			<div class="logo">
				<img src="{ISP_LOGO}" alt="i-MSCP logo" />
			</div>
		</div>
		<div class="location">
			<div class="location-area">
				<h1 class="manage_users">{TR_MENU_MANAGE_USERS}</h1>
			</div>
			<ul class="location-menu">
				<!-- BDP: logged_from -->
				<li><a class="backadmin" href="change_user_interface.php?action=go_back">{YOU_ARE_LOGGED_AS}</a></li>
				<!-- EDP: logged_from -->
				<li><a class="logout" href="../index.php?logout">{TR_MENU_LOGOUT}</a>
				</li>
			</ul>
			<ul class="path">
				<li><a href="users.php">{TR_MANAGE_USERS}</a></li>
			</ul>
		</div>

		<div class="left_menu">
			{MENU}
		</div>

		<div class="body">
			<h2 class="users"><span>{TR_MANAGE_USERS}</span></h2>

			<!-- BDP: page_message -->
			<div class="{MESSAGE_CLS}">{MESSAGE}</div>
			<!-- EDP: page_message -->

			<!-- BDP: users_search -->
			<form action="users.php" method="post" name="search_user" id="search_user">
				<a class="icon i_show_alias" href="#" onclick="return sbmt_details(document.forms[0],'{SHOW_DETAILS}');">{TR_VIEW_DETAILS}</a>
				<input name="search_for" type="text" value="{SEARCH_FOR}" />
				<select name="search_common">
					<option value="domain_name" {M_DOMAIN_NAME_SELECTED}>{M_DOMAIN_NAME}</option>
					<option value="customer_id" {M_CUSTOMER_ID_SELECTED}>{M_CUSTOMER_ID}</option>
					<option value="lname" {M_LAST_NAME_SELECTED}>{M_LAST_NAME}</option>
					<option value="firm" {M_COMPANY_SELECTED}>{M_COMPANY}</option>
					<option value="city" {M_CITY_SELECTED}>{M_CITY}</option>
					<option value="country" {M_COUNTRY_SELECTED}>{M_COUNTRY}</option>
				</select>
				<select name="search_status">
					<option value="all" {M_ALL_SELECTED}>{M_ALL}</option>
					<option value="ok" {M_OK_SELECTED}>{M_OK}</option>
					<option value="disabled" {M_SUSPENDED_SELECTED}>{M_SUSPENDED}</option>
				</select>
				<input name="Submit" type="submit" value="{TR_SEARCH}" />
				<input type="hidden" name="uaction" value="go_search" />
				<input type="hidden" name="details" value="" />
			</form>
			<!-- EDP: users_search -->
			<!-- BDP: users_list -->
			<table>
				<thead>
					<tr>
						<th>{TR_USER_STATUS}</th>
						<th>{TR_USERNAME}</th>
						<th>{TR_CREATION_DATE}</th>
						<th>{TR_DISK_USAGE}</th>
						<th>{TR_ACTION}</th>
					</tr>
				</thead>
				<tbody>
					<!-- BDP: user_entry -->
					<tr>
						<td>
							<a href="#" onclick="action_status('{URL_CHANGE_STATUS}', '{NAME}'); return false;" class="icon i_{STATUS_DOMAIN}">{STATUS_DOMAIN}</a>
						</td>
						<td>
							<!-- BDP: status_reload_true -->
							<a href="http://{NAME}/" target="_blank" class="icon i_goto">{NAME}</a>
							<!-- EDP: status_reload_true -->

							<!-- BDP: status_reload_false -->
							<span class="icon i_goto">{NAME}</span>
							<!-- EDP: status_reload_false -->
						</td>
						<td>{CREATION_DATE}</td>
						<td>{DISK_USAGE}</td>
						<td>
							<a class="icon i_identity" href="domain_details.php?domain_id={DOMAIN_ID}">{TR_DETAILS}</a>
							<!-- BDP: edit_option -->
							<a class="icon i_user" href="domain_edit.php?edit_id={DOMAIN_ID}">{TR_EDIT_DOMAIN}</a>
							<a class="icon i_edit" href="user_edit.php?edit_id={USER_ID}">{TR_EDIT_USER}</a>
							<!-- EDP: edit_option -->

							<a class="icon i_details" href="change_user_interface.php?to_id={USER_ID}">{CHANGE_INTERFACE}</a>

							<a class="icon i_stats" href="domain_statistics.php?month={VL_MONTH}&year={VL_YEAR}&domain_id={DOMAIN_ID}">{TR_STAT}</a>
							<a class="icon i_delete" href="domain_delete.php?domain_id={DOMAIN_ID}">{ACTION}</a>
						</td>
					</tr>
					<!-- BDP: user_details -->
					<tr>
						<td colspan="5">
							<a href="http://www.{ALIAS_DOMAIN}/" target="_blank" class="icon i_goto">{ALIAS_DOMAIN}</a>
						</td>
					</tr>
					<!-- EDP: user_details -->
					<!-- EDP: user_entry -->
				</tbody>
			</table>
			<!-- EDP: users_list -->
			<div class="paginator">
				<!-- BDP: scroll_next_gray -->
				<a class="icon i_next_gray" href="#">&nbsp;</a>
				<!-- EDP: scroll_next_gray -->

				<!-- BDP: scroll_next -->
				<a class="icon i_next" href="users.php?psi={NEXT_PSI}" title="{TR_NEXT}">{TR_NEXT}</a>
				<!-- EDP: scroll_next -->

				<!-- BDP: scroll_prev -->
				<a class="icon i_prev" href="users.php?psi={PREV_PSI}" title="{TR_PREVIOUS}">{TR_PREVIOUS}</a>
				<!-- EDP: scroll_prev -->

				<!-- BDP: scroll_prev_gray -->
				<a class="icon i_prev_gray" href="#">&nbsp;</a>
				<!-- EDP: scroll_prev_gray -->
			</div>
		</div>
<!-- INCLUDE "../shared/layout/footer.tpl" -->
