import {inject, Injectable} from '@angular/core';
import {HttpWrapperService} from './http-wrapper.service';
import {Supplier} from '../models/supplier.model';
import {Observable} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SupplierService {
  private endpoint = '/supplier';
  private httpWrapper = inject(HttpWrapperService);

  create(supplier: Supplier): Observable<any> {
    return this.httpWrapper.POST(`${this.endpoint}`, supplier);
  }
}
