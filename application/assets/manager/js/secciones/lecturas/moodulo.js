var Elemento_Module = {
	attrs: {
		publicaciones: [],

	},
	methods: function () {



		function checkAvailableListPublicaciones(id) {
			return Elemento_Module.attrs.publicaciones.includes(id) ? false : true;
		}

		function getAll() {
			return Elemento_Module.attrs.publicaciones;
		}

		function countList() {
			return Elemento_Module.attrs.publicaciones.length;
		}

		function display() {
			$('.publicaciones_seleccionadas').text(this.countList());
			console.log('[array publicaciones] - Lista de ids', this.getAll());
		}

		function onChangeNumberTextSelect() {
			$('.publicaciones_seleccionadas').text(this.countList());
		}

		function actionsButtons() {

			$('.remove-all-Publicaciones').click(function (e) {
				e.preventDefault();

				swal({
					title: 'Eliminar Publicaciones',
					text: "¿Esta seguro desea eliminar la(s) persona(s) seleccionada(s)?",
					type: 'error',
					showCancelButton: true,
					confirmButtonText: 'Si, eliminar',
					cancelButtonText: 'No, cancelar!',
					confirmButtonClass: 'btn btn-success',
					cancelButtonClass: 'btn btn-danger m-l-10',
					buttonsStyling: false
				}).then(function () {

					Elemento_Module.methods().multiDeletes();
				}, function () {
					swal("Cancelado!", "Operación cancelada por el usuario!", "error");
				});
			});
		}



		function insert(id) {

			if (this.checkAvailableListPublicaciones(id)) {
				Elemento_Module.attrs.publicaciones.push(id);
			}

			this.display();
			this.refresh();
		}

		function remove(id) {

			Elemento_Module.attrs.publicaciones.splice(Elemento_Module.attrs.publicaciones.indexOf(id), 1);

			this.display();
			this.refresh();
		}

		function refresh() {

			if (Elemento_Module.attrs.publicaciones.length) {

				this.onChangeNumberTextSelect();
			} else {

			}

			$('.publicaciones_seleccionadas').html(this.countList());
		}

		function clearAll() {
			Elemento_Module.attrs.publicaciones = [];
			this.display();
		}

		function clearAndRefresh() {
			Elemento_Module.methods().clearAll();

			Elemento_Module.methods().refresh();
		}

		function onAllSelectionPublicaciones() {
			this.clearAll();

		}

		function fetchIds(ids) {
			this.clearAndRefresh();

			$('.publicaciones_seleccionadas').html("");

			ids.map(r => Elemento_Module.methods().insert(r));
		}

		return {
			fetchIds: fetchIds,
			insert: insert,
			remove: remove,
			getAll: getAll,
			display: display,
			refresh: refresh,
			countList: countList,
			clearAll: clearAll,
			clearAndRefresh: clearAndRefresh,


			checkAvailableListPublicaciones: checkAvailableListPublicaciones,
			onChangeNumberTextSelect: onChangeNumberTextSelect,
			onAllSelectionPublicaciones: onAllSelectionPublicaciones
		};
	},
	init: function () {
		console.log('Module Publicaciones Loaded');

	}
};