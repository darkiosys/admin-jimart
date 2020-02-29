<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Saldo</div>
                    <div class="card-body">
						<?php if(Auth::user()->role == 0): ?>
                        <a href="<?php echo e(url('/saldo/create')); ?>" class="btn btn-success btn-sm" title="Add New Saldo">
							<i class="fa fa-plus" aria-hidden="true"></i> Topup Saldo
                        </a>
						<?php endif; ?>
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member</th>
                                        <th>Receiver</th>
                                        <th>Nominal</th>
                                        <th>Ending Saldo</th>
										<th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php echo e($item->id); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->members_id); ?> <?php echo e($item->sender); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->receiver); ?>

                                        </td>
                                        <td>
                                            Rp. <?php echo e(number_format($item->nominal, 0, ".", ".")); ?>

                                        </td>
                                        <td>
                                            Rp. <?php echo e(number_format($item->ending_saldo, 0, ".", ".")); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->date); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->status); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/jmart.co.id/resources/views/saldo/historytransfer.blade.php ENDPATH**/ ?>