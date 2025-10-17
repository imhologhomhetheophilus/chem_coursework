<?php include 'includes/header.php'; ?>

<style>
.hover-card:hover {
    cursor: pointer;
    transform: translateY(-5px);
    transition: 0.3s;
}
</style>

<div class="container py-5 bg-light text-center shadow-lg rounded" data-aos="fade-down">
    <h1 class="text-primary display-5 mb-3 animate__animated animate__backInDown">OUR PERSONNEL</h1>
    <p class="lead fs-4 mb-5">Meet our dedicated lecturers, lab technologists, and supervisors.</p>

    <div class="row justify-content-center g-4">
        <?php
        // Array of personnel (you can load from DB dynamically)
        $personnels = [
            [
                'img' => 'assets/img_lecturers/dr_salihu.png',
                'name' => 'Dr. Salihu S. Maiwalima',
                'role' => 'Senior Lecturer',
                'position' => 'Head Of Department',
                'extra' => ['MNSE, COREN','Tel: 07030699569','Email: ssalihum@gmail.com','suleimansalihum@fedpolynas.edu.ng']
            ],
            [
                'img' => 'assets/img_lecturers/dr_abdul.png',
                'name' => 'Abdulazeez A. Abdulazee',
                'role' => 'Senior Lecturer',
                'position' => 'Sub Head Of Department',
                'extra' => ['Tel: 08140976963','Email: azbinbaz@gmail.com','abdulazeezaabdulazeez@fedpolynas.edu.ng']
            ],
            [
                'img' => 'assets/img_lecturers/dr_aliu.png',
                'name' => 'Aliyu Abdulquadir',
                'role' => 'Senior Instructor',
                'position' => '',
                'extra' => ['Tel: 08069153662','Email: abdulquadiraliyu@gmail.com']
            ],
            [
                'img' => 'assets/img_lecturers/dr_jibril.png',
                'name' => 'Jibril Abubakar Ahmad',
                'role' => 'Senior Instructor',
                'position' => '',
                'extra' => ['Tel: 08054543261','Email: jibrila02@gmail.com']
            ],
            [
                'img' => 'assets/img_lecturers/dr_ibrahim.png',
                'name' => 'Ibrahim D Ibrahim',
                'role' => 'Technician',
                'position' => '',
                'extra' => ['Tel: 08038331333','Email: ibrahimibrahim65@gmail.com','ibrahimdibrahim@fedpolynas.edu.ng']
            ],
            [
                'img' => 'assets/img_lecturers/dr_sediq.png',
                'name' => 'Sadiq Joseph',
                'role' => 'Senior Technologist',
                'position' => '',
                'extra' => ['Tel: 08069123122','Email: saddypa247@gmail.com','sadiqjoseph@fedpolynas.edu.ng']
            ],
            [
                'img' => 'assets/img_lecturers/mr_sali.png',
                'name' => 'Salihu Fatima Aliyu',
                'role' => 'Lecturer III',
                'position' => '',
                'extra' => ['Tel: 07062639198','Email: fatynnhamza@gmail.com']
            ],
            [
                'img' => 'assets/img_lecturers/dr_m.jpg',
                'name' => 'Muhammad Yasir',
                'role' => 'Lab Coordinator',
                'position' => '',
                'extra' => ['Tel: 08168684298','Email: yaakzu@gmail.com']
            ],
        ];

        foreach($personnels as $p): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card hover-card h-100 shadow-sm text-center p-3">
                <img src="<?= $p['img'] ?>" 
                     class="card-img-top img-fluid rounded-circle mx-auto d-block my-3" 
                     alt="<?= htmlspecialchars($p['name']) ?>" 
                     loading="lazy" 
                     style="max-width: 120px;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                    <h6 class="text-danger"><?= htmlspecialchars($p['role']) ?></h6>
                    <?php if(!empty($p['position'])): ?>
                        <p><?= htmlspecialchars($p['position']) ?></p>
                    <?php endif; ?>
                    <?php foreach($p['extra'] as $info): ?>
                        <p class="mb-1"><?= htmlspecialchars($info) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container py-5">
    <h1 class="text-primary display-5 mb-4">LABORATORY PERSONNEL</h1>
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>S/N</th>
                    <th>Personnel</th>
                    <th>Designation</th>
                    <th>Pre.Code</th>
                    <th>Phone Number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>ABDULAZEEZ A. ABDULAZEEZ</td>
                    <td>Chief Coordinator</td>
                    <td>LAB01</td>
                    <td>08140976963</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>ALIYU ABDULQADIR</td>
                    <td>Asst. Coordinator</td>
                    <td>LAB02</td>
                    <td>08069153662</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>FATIMA S. ALIYU</td>
                    <td>Asst. Coordinator</td>
                    <td>LAB03</td>
                    <td>07062639198</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>JIBRIL A. AHMAD</td>
                    <td>Asst. Coordinator</td>
                    <td>LAB04</td>
                    <td>08054543261</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>MUHAMMAD YASIR</td>
                    <td>Asst. Coordinator</td>
                    <td>LAB05</td>
                    <td>08035965974</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>SADEEQ JOSEPH</td>
                    <td>Asst. Coordinator</td>
                    <td>LAB06</td>
                    <td>08068684298</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>IBRAHIM D IBRAHIM</td>
                    <td>Technicial</td>
                    <td>LAB07</td>
                    <td>08038331333</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>CORP MEMBER(s)</td>
                    <td></td>
                    <td>LAB08</td>
                    <td></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>8	LAB ATTENDANT</td>
                    <td>8	LAB ATTENDANT</td>
                    <td>LAB09</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="container py-5" style="margin-bottom: 10rem;"></div>

<?php include 'includes/footer.php'; ?>
