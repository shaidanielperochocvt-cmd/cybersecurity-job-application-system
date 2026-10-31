
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Job Application</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f4f6f9;
        }

        h1, h2 {
            color: #17365d;
        }

        .application-form {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #17365d;
            color: white;
        }

        .table-container {
            overflow-x: auto;
        }
    </style>
</head>

<body>

<h1>Cybersecurity Job Application System</h1>
<p>Fill out the form below to submit a job application.</p>

<div class="application-form">
    <h2>Application Form</h2>

    <form action="insert.php" method="POST">

        <label>Full Name:</label>
        <input type="text" name="full_name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Phone:</label>
        <input type="text" name="phone">

        <label>Position Applied For:</label>
        <select name="position" required>
            <option value="">Select a position</option>
            <option value="Cybersecurity Analyst">Cybersecurity Analyst</option>
            <option value="Penetration Tester">Penetration Tester</option>
            <option value="Security Engineer">Security Engineer</option>
            <option value="Network Security Specialist">Network Security Specialist</option>
        </select>
        <label>Educational Background:</label>
        <input type="text" name="education">

        <label>Cybersecurity Skills:</label>
        <textarea name="skills" rows="4"></textarea>

        <button type="submit">Submit Application</button>
    </form>
</div>

<div class="table-container">
    <?php
    require_once __DIR__ . "/render_table.php";
    ?>
</div>
</body>
</html>