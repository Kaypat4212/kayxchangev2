<table>
    <thead>
        <tr>
            <th>User</th>
            <th>Amount</th>
            <th>Account</th>
            <th>Proof</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($deposits as $deposit)
            <tr>
                <td>{{ $deposit->user->name }}</td>
                <td>{{ $deposit->amount }} NGN</td>
                <td>{{ $deposit->companyAccount->bank_name }} - {{ $deposit->companyAccount->account_number }}</td>
                <td><a href="{{ Storage::url($deposit->proof_of_payment) }}" target="_blank">View Proof</a></td>
                <td>{{ $deposit->status }}</td>
                <td>
                    <form action="{{ route('admin.deposits.update', $deposit) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <select name="status">
                            <option value="approved" {{ $deposit->status === 'approved' ? 'selected' : '' }}>Approve</option>
                            <option value="cancelled" {{ $deposit->status === 'cancelled' ? 'selected' : '' }}>Cancel</option>
                        </select>
                        <input type="text" name="admin_note" placeholder="Admin Note" value="{{ $deposit->admin_note }}">
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>