<form action="index.php" method="POST">
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= isset($edit) ? $edit['name'] : '' ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= isset($edit) ? $edit['email'] : '' ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone:</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?= isset($edit) ? $edit['phone'] : '' ?>" required>
    </div>

    <div class="form-group">
        <label for="address">Address:</label>
        <textarea class="form-control" id="address" name="address" required><?= isset($edit) ? $edit['address'] : '' ?></textarea>
    </div>

    <input type="hidden" name="id" value="<?= isset($edit) ? $edit['id'] : '' ?>">

    <div class="text-center">
	<button type="submit" name="submit" class="btn btn-primary"> <?= isset($edit) ? 'Update' : 'Add' ?> User</button>
	</div>
	
</form>
