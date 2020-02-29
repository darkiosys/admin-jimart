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
                        <b>Total Saldo : Rp. <?php echo e(number_format($totalsaldo, 0, ".", ".")); ?></b>
                        <form method="GET" action="<?php echo e(url('/member-saldo')); ?>" accept-charset="UTF-8" class="form-inline my-2 my-lg-0 float-right" role="search">
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
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Member</th>
                                        <th>Sponsor</th>
                                        <th>Username</th>
                                        <th>Saldo</th>
										<th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php echo e($item->id); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->first_name); ?> <?php echo e($item->last_name); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->sponsor); ?>

                                        </td>
                                        <td>
                                            <?php echo e($item->username); ?>

                                        </td>
                                        <td>
                                        Rp. <?php echo e(number_format($item->saldo, 0, ".", ".")); ?>

                                        </td>
                                        <td>
                                            <a href="#" onclick="setid(<?php echo e($item->id); ?>)" class="btn btn-success" data-toggle="modal" data-target="#gp">Ganti Password</a>
                                            <a href="/api/hapusmember/<?php echo e($item->id); ?>" onclick="return confirm('Yakin akan hapus member?')" class="btn btn-danger">Hapus Member</a>
                                            <a href="/api/hapussaldomember/<?php echo e($item->id); ?>" onclick="return confirm('Yakin akan kosongkan saldo member?')" class="btn btn-warning">Kosongkan Saldo</a>
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
    <div class="modal fade" id="gp" style="display: none;">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            </div>
            <form method="post" action="/user/changepassword">
                <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <input type="text" name="password" class="form-control" placeholder="masukan password baru">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default pull-left" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Ganti Password</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <script>
        function setid(val) {
            document.getElementById("id").value = val;
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/jmart.co.id/resources/views/saldo/membersaldo.blade.php ENDPATH**/ ?>