<!DOCTYPE html>
<html>
<head>
    <title>Users List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #aaa;
        }
    </style>
</head>
<body>
    <h1>Users List</h1>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role ID</th>
                <th>Profile Picture</th>
                <th>Agreed to Terms</th>
                <th>Requires Password Change</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) {
            echo '<tr>
                        <td>' . $user->user_id . '</td>
                        <td>' . $user->first_name . '</td>
                        <td>' . $user->last_name . '</td>
                        <td>' . $user->email . '</td>
                        <td>' . $user->role_id . '</td>
                        <td><img src="' . $user->profile_pic . '" alt="Profile" width="50"></td>
                        <td>' . ($user->agreed_to_terms ? 'Yes' : 'No') . '</td>
                        <td>' . ($user->requires_password_change ? 'Yes' : 'No') . '</td>
                    </tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>
