import {inject, Injectable} from '@angular/core';
import {HttpWrapperService} from './http-wrapper.service';
import {Supplier} from '../models/supplier.model';
import {Observable} from 'rxjs';
import {SupplierFilters, SupplierSortingOptions} from '../interfaces/supplier.interfaces';
import {PaginationOptions} from '../utils/app.helpers';
import {PaginatedResponse} from '../entities/paginated-response.entity';
import {HttpParams} from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class SupplierService {
  private endpoint = '/supplier';
  private httpWrapper = inject(HttpWrapperService);

  create(supplier: Supplier): Observable<any> {
    return this.httpWrapper.POST(`${this.endpoint}`, supplier);
  }

  get(filters: SupplierFilters = {},
      pagination: PaginationOptions = {},
      sorting: SupplierSortingOptions = {}): Observable<PaginatedResponse<Supplier>> {
    let params = new HttpParams();

    // Filtros!
    if (filters.name) params = params.set('name', filters.name);
    if (filters.ruc) params = params.set('ruc', filters.ruc);
    if (filters.email) params = params.set('email', filters.email);

    // Paginado!
    if (pagination.page) params = params.set('page', pagination.page.toString());
    if (pagination.per_page) params = params.set('per_page', pagination.per_page.toString());

    // Ordenado!
    if (sorting.sort_by) params = params.set('sort_by', sorting.sort_by);
    if (sorting.sort_dir) params = params.set('sort_dir', sorting.sort_dir);

    return this.httpWrapper.GET<PaginatedResponse<Supplier>>(`${this.endpoint}`, {params});
  }
}
