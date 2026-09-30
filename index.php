<?php
session_start();

include 'config/database.php';

// --------------------------------------------------
// CHECK IF USER IS ALREADY LOGGED IN
// --------------------------------------------------
if (isset($_SESSION['user_id'])) {

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: student/dashboard.php");
        exit;
    }
}

// --------------------------------------------------
// LOGIN PROCESS
// --------------------------------------------------
$error = '';

if (isset($_POST['login'])) {

    // Get form values
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if fields are empty
    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        // --------------------------------------------------
        // GET USER FROM DATABASE
        // --------------------------------------------------
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password, full_name, role
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $error = 'Something went wrong. Please try again later.';

        } else {

            // Bind username to query
            mysqli_stmt_bind_param($stmt, "s", $username);

            // Execute query
            mysqli_stmt_execute($stmt);

            // Get result
            $result = mysqli_stmt_get_result($stmt);

            // --------------------------------------------------
            // CHECK IF USER EXISTS
            // --------------------------------------------------
            if (mysqli_num_rows($result) === 1) {

                // Get user record
                $user = mysqli_fetch_assoc($result);

                // --------------------------------------------------
                // VERIFY PASSWORD
                // --------------------------------------------------
                if (password_verify($password, $user['password'])) {

                    // --------------------------------------------------
                    // LOGIN SUCCESS
                    // --------------------------------------------------

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Store user information in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];

                    // --------------------------------------------------
                    // REDIRECT BASED ON USER ROLE
                    // --------------------------------------------------
                    if ($user['role'] === 'admin') {

                        header("Location: admin/dashboard.php");
                        exit;

                    } else {

                        header("Location: student/dashboard.php");
                        exit;
                    }

                } else {

                    // Wrong password
                    $error = 'Invalid username or password.';
                }

            } else {

                // Username does not exist
                $error = 'Invalid username or password.';
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Login - Student Portal</title>

    <!-- Bootstrap CSS -->
    <link
        href="assets/vendor/bootstrap/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <link
        href="assets/css/style.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container">

    <div class="login-box">

        <div class="card">

            <div class="card-body p-4">

                <!-- TITLE -->
                <h2 class="text-center">
                    Student Portal
                </h2>

                <p class="text-center text-muted">
                    Admin and Student Login
                </p>


                <!-- ERROR MESSAGE -->
                <?php if ($error !== ''): ?>

                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <!-- LOGIN FORM -->
                <form method="POST" action="">

                    <!-- USERNAME -->
                    <div class="mb-3">

                        <label
                            for="username"
                            class="form-label"
                        >
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->
                    <div class="mb-3">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <!-- LOGIN BUTTON -->
                    <button
                        type="submit"
                        name="login"
                        class="btn btn-primary w-100"
                    >
                        Login
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>
