<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Transaksi PPOB</div>
                    <div class="card-body">
                    <form method="GET" action="<?php echo e(url('/ppob')); ?>" accept-charset="UTF-8" class="form-inline my-2 my-lg-0 float-right" role="search">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo e(request('search')); ?>">
                                <span class="input-group-append">
                                    <button class="btn btn-secondary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                        <br>
                        <br>
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Member</th>
                                        <th>Transaksi</th>
                                        <th>Tujuan</th>
                                        <th>Total Tagihan</th>
                                        <th>Ending Saldo</th>
                                        <th>Produk</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $ppob; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        if($item->status == "Gagal") {
                                    ?>
                                    <tr style="background-color: red">
                                    <?php
                                        } else {
                                            echo "<tr>";
                                        }
                                    ?>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($item->members_id); ?></td>
                                        <td><?php echo e($item->trx_name); ?></td>
                                        <td><?php echo e($item->no_hp); ?></td>
                                        <td><?php echo e($item->total_tagihan); ?></td>
                                        <td><?php echo e($item->ending_saldo); ?></td>
                                        <td><?php echo e($item->product_code); ?></td>
                                        <td><?php echo e($item->trx_date); ?></td>
                                        <td><?php echo e($item->status); ?></td>
                                        <td>
                                            <a class="btn btn-info btn-sm"  href="/ppob/delete?id=<?php echo e($item->id); ?>" onclick="return confirm(&quot;Hapus Transaksi?&quot;)">delete</a> <br /><br />
                                            <?php if($item->status == "Berhasil"): ?>
                                            <a class="btn btn-danger btn-sm"  href="/ppob/return?id=<?php echo e($item->id); ?>" onclick="return confirm(&quot;Transaksi Gagal, Kembalikan Saldo?&quot;)">return</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> <?php echo $ppob->appends(['search' => Request::get('search')])->render(); ?> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/jmart.co.id/resources/views/saldo/ppob.blade.php ENDPATH**/ ?>