import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { ApiService } from '../services/api.service';
import { API_response, Product } from '../types';

@Injectable({
    providedIn: 'root'
})

export class ProductState {

    products: Product[] = undefined;

    products$: BehaviorSubject<Product[]> = new BehaviorSubject<Product[]>( this.products );

    constructor( private api: ApiService ) {}

    getProducts() {
        //call types.ts service and pass endpoint and object
        this.api.post( 'products/read' ).subscribe( 
            ( response: API_response ) => {
            
                //thow error if not successfull
                if ( response.success !== true )
                {
                    alert( response.message );
                    return;
                }
                console.log( response.data);
                this.setProductState( response.data );
                
            }
        );   
    }

    addProduct( new_product: Product ) {

        this.products.push( new_product );

        this.setProductState( this.products );

    }

    removeProduct( deleted_product_id: number ) {

        this.products.forEach( 
            ( value, index ) => {
                if ( value.id == deleted_product_id ) 
                    this.products.splice( index, 1 );
            }
        );

        this.setProductState( this.products );

    }

    setProductState( products_incoming: Product[] ) {

        this.products = products_incoming;

        this.products$.next( this.products );

    }

}
