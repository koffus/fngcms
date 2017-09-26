{{ error }}
<form name="form" action="" method="post">

	<fieldset>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-4">
					Вопрос
				</div>
				<div class="col-sm-8">
					<textarea type="text" name="question" rows="4" class="form-control">{{ question }}</textarea>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-sm-4">
					Ответ
				</div>
				<div class="col-sm-8">
					<textarea type="text" name="answer" rows="4" class="form-control">{{ answer }}</textarea>
				</div>
			</div>
		</div>
	</fieldset>

	<div class="well text-center">
		<input type="reset" value="Сброс" class="btn btn-default" /> 
		<input type="submit" name="submit" value="{{ lang['edit'] }}" class="btn btn-success" />
	</div>
</form>