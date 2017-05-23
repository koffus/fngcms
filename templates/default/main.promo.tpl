{% if isHandler('news:main') %}
	<div id="myCarousel" class="carousel slide" data-ride="carousel">
		<ol class="carousel-indicators">
			<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
			<li data-target="#myCarousel" data-slide-to="1" class=""></li>
		</ol>
		<div class="carousel-inner" role="listbox">
			<div class="carousel-item active">
				<img src="{{ tpl_url }}/img/slider/slide-1.jpg" alt="First slide" />
				<div class="container">
					<div class="carousel-caption">
						<h2>Example headline.</h2>
						<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
						<p><a class="btn btn-primary" href="#" role="button">Sign up today</a></p>
					</div>
				</div>
			</div>
			<div class="carousel-item">
				<img src="{{ tpl_url }}/img/slider/slide-2.jpg" alt="Second slide" />
				<div class="container">
					<div class="carousel-caption">
						<h2>Another example headline.</h2>
						<p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
						<p><a class="btn btn-primary" href="#" role="button">Learn more</a></p>
					</div>
				</div>
			</div>
		</div>
		<a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev"><span class="carousel-control-prev-icon"></span></a>
		<a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next"><span class="carousel-control-next-icon"></span></a>
	</div>

	<div class="container marketing">
		<div class="row">
			<div class="col-lg-4">
				<div class="rounded-circle justify-center">
					<em class="fa fa-2x fa-paint-brush"></em>
				</div>
				<h2>Clean Design</h2>
				<p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</p>
				<p><a class="btn btn-outline-primary" href="#" role="button">View details »</a></p>
			</div>
			<div class="col-lg-4">
				<div class="rounded-circle justify-center">
					<em class="fa fa-2x fa-mobile"></em>
				</div>
				<h2>Responsive</h2>
				<p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</p>
				<p><a class="btn btn-outline-primary" href="#" role="button">View details »</a></p>
			</div>
			<div class="col-lg-4">
				<div class="rounded-circle justify-center">
					<em class="fa fa-2x fa-code"></em>
				</div>
				<h2>Bootstrap 4</h2>
				<p>Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh.</p>
				<p><a class="btn btn-outline-primary" href="#" role="button">View details »</a></p>
			</div>
		</div>
	</div>

{% endif %}