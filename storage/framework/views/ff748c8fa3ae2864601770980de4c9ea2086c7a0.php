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
                        <form method="GET" action="<?php echo e(url('/saldo')); ?>" accept-charset="UTF-8" class="form-inline my-2 my-lg-0 float-right" role="search">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo e(request('search')); ?>">
                                <span class="input-group-append">
                                    <button class="btn btn-secondary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>

                        <br/>
                        <br/>
						<?php if(Auth::user()->role == 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th><th>No Rek</th><th>Topup Saldo</th><th>Nominal Transfer</th>
										<?php if(Auth::user()->role == 1): ?>
										<th>Actions</th>
										<?php else: ?>
										<th>Status</th>
										<?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $saldo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($item->no_rek); ?></td><td>Rp. <?php echo e(number_format($item->saldo)); ?></td><td>Rp. <?php echo e(number_format($item->jumlah_transfer)); ?></td>
										<?php if(Auth::user()->role == 1): ?>
                                        <td>
                                            <a href="<?php echo e(url('/saldo/' . $item->id)); ?>" title="View Saldo"><button class="btn btn-info btn-sm"><i class="fa fa-eye" aria-hidden="true"></i> View</button></a>
                                            <a href="<?php echo e(url('/saldo/' . $item->id . '/edit')); ?>" title="Edit Saldo"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>

                                            <form method="POST" action="<?php echo e(url('/saldo' . '/' . $item->id)); ?>" accept-charset="UTF-8" style="display:inline">
                                                <?php echo e(method_field('DELETE')); ?>

                                                <?php echo e(csrf_field()); ?>

                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Saldo" onclick="return confirm(&quot;Confirm delete?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
                                            </form>
                                        </td>
										<?php else: ?>
										<td>
                                            <?php if($item->status == 0): ?>
												Menunggu verifikasi
											<?php elseif($item->status == 1): ?>
												Sudah Di verifikasi
											<?php endif; ?>
                                        </td>
										<?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> <?php echo $saldo->appends(['search' => Request::get('search')])->render(); ?> </div>
                        </div>
						<?php else: ?>
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th><th>Nama Member</th><th>Topup Saldo</th><th>Nominal Transfer</th><th>Transfer Ke</th>
										<?php if(Auth::user()->role == 1): ?>
										<th>Actions</th>
										<?php else: ?>
										<th>Status</th>
										<?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $saldo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($item->first_name); ?> <?php echo e($item->last_name); ?></td>
										<td>Rp. <?php echo e(number_format($item->saldo)); ?></td>
										<td>Rp. <?php echo e(number_format($item->jumlah_transfer)); ?></td>
										<td><?php echo e($item->no_rek); ?></td>
										<?php if(Auth::user()->role == 1): ?>
                                        <td>
											<?php if($item->status == 0): ?>
												<form method="POST" action="<?php echo e(url('/saldo/verifikasi' . '/' . $item->id)); ?>" accept-charset="UTF-8" style="display:inline">    
													<?php echo e(csrf_field()); ?>

													<button type="submit" class="btn btn-primary btn-sm" title="Verifikasi" onclick="return confirm(&quot;Verifikasi topup?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Verifikasi</button>
												</form>
                                                <form method="POST" action="<?php echo e(url('/saldo/delete' . '/' . $item->id)); ?>" accept-charset="UTF-8" style="display:inline">    
													<?php echo e(csrf_field()); ?>

													<button type="submit" class="btn btn-danger btn-sm" title="X" onclick="return confirm(&quot;Hapus Request?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> X</button>
												</form>
											<?php elseif($item->status == 1): ?>
												Sudah Di verifikasi
											<?php endif; ?>
                                        </td>
										<?php else: ?>
										<td>
                                            <?php if($item->status == 0): ?>
												Menunggu verifikasi
											<?php elseif($item->status == 1): ?>
												Sudah Di verifikasi
											<?php endif; ?>
                                        </td>
										<?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> <?php echo $saldo->appends(['search' => Request::get('search')])->render(); ?> </div>
                        </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/jmart.co.id/resources/views/saldo/index.blade.php ENDPATH**/ ?>