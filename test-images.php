<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unity Lab - Image Verification Map</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .image-map-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        .image-map-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .image-map-card img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            border-radius: 4px;
            background-color: #cbd5e1;
            margin-bottom: 10px;
        }
        .image-map-card code {
            background-color: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: #0f172a;
        }
    </style>
</head>
<body style="padding: 40px 0;">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Verification Hub</span>
            <h2 class="section-title">Mapped Laboratory Photos</h2>
            <p class="section-desc">Verify how your uploaded diagnostic images have been resized and mapped to the website theme paths.</p>
        </div>

        <div class="image-map-grid">
            <div class="image-map-card">
                <img src="images/eq-biochem.jpg" alt="Biochemistry Analyzer">
                <h3>Biochemistry Analyzer</h3>
                <code>images/eq-biochem.jpg</code>
            </div>
            
            <div class="image-map-card">
                <img src="images/eq-centrifuge.jpg" alt="Centrifuge">
                <h3>REMI Centrifuge</h3>
                <code>images/eq-centrifuge.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/eq-hema.jpg" alt="Hematology Analyzer">
                <h3>Hematology Cell Counter</h3>
                <code>images/eq-hema.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/eq-microscope.jpg" alt="Microscope">
                <h3>LABOMED Microscope</h3>
                <code>images/eq-microscope.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/gallery-3.jpg" alt="Pipettes and Tubes">
                <h3>Pipettes & Tube Rack</h3>
                <code>images/gallery-3.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/gallery-1.jpg" alt="Blood Draw 1">
                <h3>Phlebotomy Draw (Wide)</h3>
                <code>images/gallery-1.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/gallery-2.jpg" alt="Blood Draw 2">
                <h3>Phlebotomy Draw (Vertical)</h3>
                <code>images/gallery-2.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/cert-akshay.jpg" alt="MLT Certificate">
                <h3>Provisional Certificate</h3>
                <code>images/cert-akshay.jpg</code>
            </div>

            <div class="image-map-card">
                <img src="images/gallery-4.jpg" alt="Door sign / shelf">
                <h3>Branding Sign Poster</h3>
                <code>images/gallery-4.jpg</code>
            </div>
        </div>

        <div class="text-center" style="margin-top: 40px;">
            <a href="index.php" class="btn btn-primary"><i class="fa-solid fa-house"></i> Go to Homepage</a>
            <a href="admin/index.php" class="btn btn-teal"><i class="fa-solid fa-sliders"></i> Go to Admin Panel</a>
        </div>
    </div>
</body>
</html>
