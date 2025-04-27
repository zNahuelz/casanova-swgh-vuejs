export interface SupplierFilters {
  name?: string;
  ruc?: string;
  email?: string;
}

export interface SupplierSortingOptions {
  sort_by?: 'id' | 'name' | 'ruc' | 'email' | 'created_at';
  sort_dir?: 'asc' | 'desc';
}
