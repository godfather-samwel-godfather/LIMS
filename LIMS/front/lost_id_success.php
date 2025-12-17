<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Submitted</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../styles/admin.css">
  <style>
    body { background: #f4f6f8; font-family: 'Segoe UI', Arial, sans-serif; }
    .success-container {
      max-width: 420px;
      margin: 60px auto;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.09);
      padding: 36px 32px 28px 32px;
      text-align: center;
    }
    .success-container h2 { color: #9333B9; margin-bottom: 18px; }
    .track-number {
      font-size: 1.5rem;
      font-weight: 600;
      color: #17406a;
      background: #e3f0fb;
      border-radius: 7px;
      padding: 10px 0;
      margin: 18px 0 10px 0;
      letter-spacing: 1px;
    }
    .success-container a {
      color: #9333B9;
      text-decoration: underline;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="success-container">
    <h2>Report Submitted Successfully!</h2>
    <div>Your tracking number is:</div>
    <div class="track-number"><?php echo htmlspecialchars($_GET['track']); ?></div>
    <div style="margin-top:18px;">Please save this number to track your request status.</div>
    <div style="margin-top:24px;">
      <a href="track_status.php">Track Your ID Request</a>
    </div>
  </div>
</body>
</html>
