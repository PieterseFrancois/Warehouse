import { Component, OnDestroy, OnInit } from '@angular/core';
import { API_response, Product} from 'src/app/global/types';
import { ApiService } from 'src/app/global/services/api.service';
import { UserState } from 'src/app/global/state/user-state';
import { ProductState } from 'src/app/global/state/product-state';
import { last, Subject, takeUntil } from 'rxjs';
import { routes } from 'src/app/app-routing.module';

@Component({
    selector: 'app-products-view',
    templateUrl: './products-view.component.html',
    styleUrls: ['./products-view.component.scss']
})

export class ProductsViewComponent implements OnDestroy {

    add_products_route: string = routes.find( element => element.title === 'AddProducts' ).path;
    
    product_keys: any[] = [];
    product_values: any[] = [];
    products_loaded: boolean = false;
    is_admin: boolean = this.user_state.current_user.role === 'admin';
    
    dt_options: DataTables.Settings = {
        retrieve: true,

        pagingType: 'simple_numbers',
        
        columnDefs: [{
            targets: this.product_keys.length -1,
            orderable: false,
        }],
    };

    dt_trigger: Subject<any> = new Subject<any>();
    private destroy$: Subject<boolean> = new Subject();

    constructor( private api: ApiService,
                 private user_state: UserState,
                 private product_state: ProductState )
    {
        this.product_state.products$
        .pipe( takeUntil( this.destroy$ ) )
        .subscribe(
            ( products: Product[] ) => {

                if ( products === undefined ) {
                    this.products_loaded = false;
                    return;
                }

                this.deconstructData( products );
                this.products_loaded = true;
            }     
        ); 
    }
            
    ngOnDestroy(): void {
        this.destroy$.next(true);
        this.destroy$.complete();
    }

    deconstructData( data: any[] ) {

        //get keys from first element -> working with assumption that all elements have the same keys
        this.product_keys = Object.keys( data[0] );
        this.product_values = [];

        //get row values
        data.forEach( 
            ( element ) => {
                this.product_values.push( Object.values( element ) );
            }
        );
    }

    delete( id: number, name: string ) {

        //confirm action
        const confirmation_delete = confirm( `You are about to remove a product with the name '${name}'. Do you wish to proceed?` );

        if ( !confirmation_delete ) return;

        //setup body of request
        const body = { id: id };

        //call types.ts service and pass endpoint and object
        this.api.post( 'products/delete', body ).subscribe(
            ( response: API_response ) => {

                //thow error if not successfull
                if (response.success !== true) {
                    alert(response.message);
                    return;
                }

                //temp
                alert( 'Product removed successfully' )

                //reload table
                this.product_state.removeProduct( id );

            }
        );
    }

}
