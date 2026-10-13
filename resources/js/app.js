//

const ingredientOptions = document.querySelector('#ingredient-options');

if (ingredientOptions) {
	document.querySelectorAll('[data-recipe-form]').forEach((form) => {
		const recipeLines = form.querySelector('[data-recipe-lines]');
		const addLineButton = form.querySelector('[data-add-line]');

		if (!recipeLines || !addLineButton) return;

		let nextLineIndex = recipeLines.children.length;

		const updateUnitLabel = (select) => {
			const label = select.closest('[data-recipe-line]').querySelector('[data-line-unit]');
			label.textContent = select.selectedOptions[0]?.dataset.unit || 'unidad base';
		};

		addLineButton.addEventListener('click', () => {
			const line = document.createElement('div');
			line.className = 'recipe-line';
			line.dataset.recipeLine = '';
			line.innerHTML = `
				<label class="field">Ingrediente
					<select name="items[${nextLineIndex}][ingredient_id]" required>
						<option value="">Seleccionar ingrediente</option>${ingredientOptions.innerHTML}
					</select>
				</label>
				<label class="field">Cantidad (<span data-line-unit>unidad base</span>)
					<input type="number" name="items[${nextLineIndex}][quantity]" min="0.0001" step="0.0001" placeholder="0" required>
				</label>
				<button class="icon-button line-remove" type="button" data-remove-line title="Quitar línea" aria-label="Quitar ingrediente">×</button>`;
			recipeLines.append(line);
			nextLineIndex += 1;
		});

		recipeLines.addEventListener('change', (event) => {
			if (event.target.matches('select[name^="items"]')) updateUnitLabel(event.target);
		});

		recipeLines.addEventListener('click', (event) => {
			const removeButton = event.target.closest('[data-remove-line]');
			if (!removeButton) return;

			const line = removeButton.closest('[data-recipe-line]');
			if (recipeLines.children.length === 1) {
				line.querySelector('select').value = '';
				line.querySelector('input').value = '';
				updateUnitLabel(line.querySelector('select'));
				return;
			}

			line.remove();
		});
	});
}
