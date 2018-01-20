<table>
    <thead>
        <tr>
            <th>Datetime</th>
            <th>Action</th>
            <th>Method</th>
            <th>Status</th>
            <th>Content-Type</th>
            <th>URI</th>
            <th>Is AJAX?</th>
        </tr>
    </thead>
    <tbody>
    {files}
        <tr>
            <td>{datetime}</td>
            <td>
            	<button id="ci-history">Load</button>
            </td>
            <td>{status}</td>
            <td>{status}</td>
            <td>{status}</td>
            <td>{status}</td>
            <td>{status}</td>
        </tr>
    {/files}
    </tbody>
</table>
