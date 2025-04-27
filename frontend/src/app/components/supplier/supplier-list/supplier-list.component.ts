import {Component, inject} from '@angular/core';
import {SupplierService} from '../../../services/supplier.service';
import {Supplier} from '../../../models/supplier.model';
import {handleKeywordKeydown, reloadPage} from '../../../utils/app.helpers';
import {NgClass} from '@angular/common';
import {SUPPLIER_SEARCH_MODES} from '../../../utils/app.constants';
import {FormControl, FormGroup, FormsModule, ReactiveFormsModule, Validators} from '@angular/forms';
import {integerOnlyValidator} from '../../../validators/custom-validators';
import {SupplierFilters} from '../../../interfaces/supplier.interfaces';

@Component({
  selector: 'app-supplier-list',
  imports: [
    NgClass,
    ReactiveFormsModule,
    FormsModule,
  ],
  templateUrl: './supplier-list.component.html',
  styleUrl: './supplier-list.component.css'
})
export class SupplierListComponent {
  private supplierService = inject(SupplierService);
  protected readonly SUPPLIER_SEARCH_MODES = SUPPLIER_SEARCH_MODES;
  isLoading = false;
  loadError = false;
  suppliers: Supplier[] = [];

  hidePagination = false;
  totalItems = 0;
  currentPage = 1;
  totalPages = 1;
  pageSize = 10;
  lastPage = 1;

  searchForm = new FormGroup({
    keyword: new FormControl('', [Validators.required, Validators.minLength(1)]),
    searchMode: new FormControl(0, [Validators.required]),
  });

  ngOnInit() {
    this.loadSuppliers();
    this.handleSearchMode(this.searchForm.value.searchMode!!);
  }

  loadSuppliers(filters: SupplierFilters = {}) {
    this.isLoading = true;
    this.supplierService.get(filters, {page: this.currentPage}).subscribe({
      next: (res) => {
        this.suppliers = res.data;
        this.totalPages = res.last_page;
        this.totalItems = res.total;
        this.isLoading = false;
      },
      error: err => {
        console.log(err);
        this.isLoading = false;
        this.loadError = true;
      }
    });
  }

  onSubmit() {
    const keyword = this.searchForm.value.keyword;
    let filters: SupplierFilters = {};
    switch (parseInt(this.searchForm.value.searchMode!!.toString())) {
      case 0:
        //By ID
        break;
      //By name
      case 1:
        filters = {name: keyword!!}
        this.currentPage = 1;
        this.loadSuppliers(filters);
        break;
      //By RUC
      case 2:
        filters = {ruc: keyword!!}
        this.currentPage = 1;
        this.loadSuppliers(filters);
        break;
      default:
        break;
    }

    console.log(this.searchForm.value);
  }

  handleSearchMode(val: number) {
    const keywordControl = this.searchForm.get('keyword');
    switch(val){
      case 0:
        keywordControl?.patchValue('');
        keywordControl?.setValidators([Validators.required, Validators.minLength(1)]);
        keywordControl?.updateValueAndValidity();
        break;
      case 1:
        keywordControl?.patchValue('');
        keywordControl?.setValidators([Validators.required, Validators.minLength(3)]);
        keywordControl?.updateValueAndValidity();
        break;
      case 2:
        keywordControl?.patchValue('');
        keywordControl?.setValidators([Validators.required, Validators.minLength(11), Validators.maxLength(11), Validators.pattern(/^(10|20)\d{9}$/)]);
        keywordControl?.updateValueAndValidity();
        break;
      default: break;
    }

  }

  handleKeydown(e: KeyboardEvent, searchMode: number) {
    if (searchMode === 0) {
      handleKeywordKeydown(e);
    }
  }


  nextPage(): void {
    if (this.currentPage < this.totalPages) {
      this.currentPage++;
      this.loadSuppliers();
    }
  }

  prevPage(): void {
    if (this.currentPage > 1) {
      this.currentPage--;
      this.loadSuppliers();
    }
  }


  protected readonly reloadPage = reloadPage;
}
