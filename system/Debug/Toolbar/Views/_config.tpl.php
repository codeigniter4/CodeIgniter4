<p class="debug-bar-alignRight">
	<a href="https://bcit-ci.github.io/CodeIgniter4/index.html" target="_blank" >Read the CodeIgniter docs...</a>
</p>

<table>
	<tbody>
		<tr>
			<td>CodeIgniter Version:</td>
			<td>{ ciVersion }</td>
		</tr>
		<tr>
			<td>PHP Version:</td>
			<td>{ phpVersion }</td>
		</tr>
		<tr>
			<td>PHP SAPI:</td>
			<td>{ phpSAPI }</td>
		</tr>
		<tr>
			<td>Environment:</td>
			<td>{ environment }</td>
		</tr>
		<tr>
			<td>Base URL:</td>
			<td>
				{ if $baseURL == '' }
					<div class="warning">
						The $baseURL should always be set manually to prevent possible URL personification from external parties.
					</div>
				{ else }
					{ baseURL }
				{ endif }
			</td>
		</tr>
		<tr>
			<td>TimeZone:</td>
			<td>{ timezone }</td>
		</tr>
		<tr>
			<td>Locale:</td>
			<td>{ locale }</td>
		</tr>
		<tr>
			<td>Content Security Policy Enabled:</td>
			<td>{ if $cspEnabled } Yes { else } No { endif }</td>
		</tr>
		<tr>
			<td>Salt Set?:</td>
			<td>
				{ if $salt == '' }
					<div class="warning">
						You have not defined an application-wide $salt. This could lead to a less secure site.
					</div>
				{ else }
					Set
				{ endif }
			</td>
		</tr>
	</tbody>
</table>
