<?php
/**
 * Contact
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
require_once __DIR__ . '/includes/init.php';
include('./includes/header.php');
?>

<section class="py-5">
    <div class="container">

        <nav class="breadcrumbs mb-4">
            <a href="index.php">Home</a> / <span>Contact Us</span>
        </nav>

        <h1 class="mb-5">Contact Us</h1>

        <div class="row g-4">

            <!-- Contact Info -->
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <h5 class="mb-4">Get in Touch</h5>

                    <div class="d-flex gap-3 mb-3">
                        <i class="fa-solid fa-location-dot fa-lg mt-1" style="color:var(--color-accent)"></i>
                        <div>
                            <strong>Address</strong><br>
                            173 Lucky road, LE1 3BK, Leicester
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <i class="fa-solid fa-envelope fa-lg mt-1" style="color:var(--color-accent)"></i>
                        <div>
                            <strong>Email</strong><br>
                            <a href="mailto:info@fixerupper.co.uk">info@fixerupper.co.uk</a>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <i class="fa-solid fa-phone fa-lg mt-1" style="color:var(--color-accent)"></i>
                        <div>
                            <strong>Phone</strong><br>
                            <a href="tel:073988753">073988753</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Working Hours -->
            <div class="col-md-6">
                <div class="card p-4 h-100">
                    <h5 class="mb-4">
                        <i class="fa-regular fa-clock me-2" style="color:var(--color-accent)"></i>
                        Working Hours
                    </h5>

                    <table class="table table-borderless mb-0">
                        <tbody>
                            <?php
                            $hours = [
                                'Monday'    => '10:00 – 18:00',
                                'Tuesday'   => '10:00 – 18:00',
                                'Wednesday' => '10:00 – 18:00',
                                'Thursday'  => '10:00 – 18:00',
                                'Friday'    => '10:00 – 17:00',
                                'Saturday'  => '11:00 – 14:00',
                                'Sunday'    => 'Closed',
                            ];
                            $today = date('l');
                            foreach ($hours as $day => $time):
                                $is_today  = $day === $today;
                                $is_closed = $time === 'Closed';
                            ?>
                            <tr <?= $is_today ? 'class="fw-bold"' : '' ?>>
                                <td><?= $day ?><?= $is_today ? ' <small class="text-muted">(today)</small>' : '' ?></td>
                                <td class="text-end <?= $is_closed ? 'text-danger' : 'text-success' ?>">
                                    <?= $is_closed
                                        ? '<i class="fa-solid fa-xmark me-1"></i>' . $time
                                        : '<i class="fa-solid fa-check me-1"></i>' . $time ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>